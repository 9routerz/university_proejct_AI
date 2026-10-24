import uuid
import time
import secrets
import logging
from fastapi import FastAPI, HTTPException, Depends, Header
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from config import (
    validate_config, COMPETITION_TIME_LIMIT, SESSION_TTL,
    BACKEND_SECRET, ADMIN_KEY, CORS_ORIGINS, SHOW_DOCS,
)
from tools import r, create_sandbox, kill_sandbox, get_session_meta
from agent import run_agent

# ── Validate env on startup ───────────────────────────────────────────────────
validate_config()

logging.basicConfig(level=logging.INFO, format="%(levelname)s:     %(name)s - %(message)s")
logging.getLogger("httpx").setLevel(logging.WARNING)
log = logging.getLogger("ds-api")

# ── App ───────────────────────────────────────────────────────────────────────
app = FastAPI(
    title="DS Competition API",
    description="Backend for the Air University Data Science Competition",
    version="1.0.0",
    docs_url="/docs" if SHOW_DOCS else None,
    redoc_url=None,
    openapi_url="/openapi.json" if SHOW_DOCS else None,
)

# Browsers never call this API directly (the PHP UI calls it server-side),
# so CORS is closed unless CORS_ORIGINS lists specific sites.
if CORS_ORIGINS:
    app.add_middleware(
        CORSMiddleware,
        allow_origins=CORS_ORIGINS,
        allow_methods=["GET", "POST"],
        allow_headers=["Content-Type", "X-Backend-Secret"],
    )


# ── Auth ──────────────────────────────────────────────────────────────────────
def require_backend_secret(x_backend_secret: str = Header(default="")):
    """Only callers that know BACKEND_SECRET (our PHP UI) can use the API."""
    if not secrets.compare_digest(x_backend_secret.encode(), BACKEND_SECRET.encode()):
        raise HTTPException(status_code=401, detail="Unauthorized.")


def require_admin_key(x_admin_key: str = Header(default="")):
    if not ADMIN_KEY or not secrets.compare_digest(x_admin_key.encode(), ADMIN_KEY.encode()):
        raise HTTPException(status_code=401, detail="Unauthorized.")


# ── Request / Response models ─────────────────────────────────────────────────
class StartRequest(BaseModel):
    participant_name: str = Field(min_length=1, max_length=60)


class StartResponse(BaseModel):
    session_id: str
    message: str
    time_limit_seconds: int


class ChatRequest(BaseModel):
    session_id: str = Field(max_length=64)
    message: str = Field(min_length=1, max_length=2000)


class ChatResponse(BaseModel):
    response: str
    image_base64: str | None
    intent: str
    time_remaining_seconds: int


class ScoreResponse(BaseModel):
    score: int
    feedback: str
    breakdown: str


class StatusResponse(BaseModel):
    session_id: str
    participant_name: str
    action_count: int
    time_remaining_seconds: int
    score_used: bool


# ── Helpers ───────────────────────────────────────────────────────────────────
def get_time_remaining(session_id: str) -> int:
    """Returns seconds remaining in the competition for this session."""
    start_time = r.hget(f"session:{session_id}", "start_time")
    if not start_time:
        # if start_time missing, return full time so session isn't killed
        return COMPETITION_TIME_LIMIT
    elapsed = time.time() - float(start_time)
    remaining = COMPETITION_TIME_LIMIT - elapsed
    return max(0, int(remaining))


def is_valid_session_id(session_id: str) -> bool:
    try:
        return str(uuid.UUID(session_id)) == session_id
    except ValueError:
        return False


def session_exists(session_id: str) -> bool:
    if not is_valid_session_id(session_id):
        return False
    return r.exists(f"session:{session_id}") == 1


def validate_session(session_id: str):
    """Raise HTTPException if session is invalid or time is up."""
    if not session_exists(session_id):
        raise HTTPException(status_code=404, detail="Session not found. Please start a new session.")
    remaining = get_time_remaining(session_id)
    if remaining == 0:
        kill_sandbox(session_id)
        raise HTTPException(status_code=403, detail="⏰ Time is up! Your session has ended.")


# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.post("/start", response_model=StartResponse, dependencies=[Depends(require_backend_secret)])
async def start_session(req: StartRequest):
    """
    Start a new competition session for a participant.
    Creates an isolated E2B sandbox and stores session in Redis.
    """
    session_id = str(uuid.uuid4())

    try:
        # Create sandbox first
        create_sandbox(session_id)

        # Now update the same Redis key with participant info + start time
        # using hset which merges fields, not overwrites
        r.hset(f"session:{session_id}", mapping={
            "participant_name": req.participant_name,
            "start_time": str(time.time()),
        })

        # Set final TTL — this overrides the short one set in create_sandbox
        r.expire(f"session:{session_id}", SESSION_TTL)

        return StartResponse(
            session_id=session_id,
            message=f"Welcome {req.participant_name}! Your session has started. You have {COMPETITION_TIME_LIMIT // 60} minutes. The dataset is loaded as `df`. Good luck! 🚀",
            time_limit_seconds=COMPETITION_TIME_LIMIT,
        )

    except Exception:
        log.exception("request failed")
        raise HTTPException(status_code=500, detail="Failed to start session. Please try again.")


@app.post("/chat", response_model=ChatResponse, dependencies=[Depends(require_backend_secret)])
async def chat(req: ChatRequest):
    """
    Send a message to the agent.
    The agent will interpret it, run code in the sandbox, and return the result.
    """
    validate_session(req.session_id)

    try:
        result = run_agent(req.session_id, req.message)

        return ChatResponse(
            response=result["response"],
            image_base64=result.get("image_base64") or None,
            intent=result.get("intent", "code"),
            time_remaining_seconds=get_time_remaining(req.session_id),
        )

    except Exception:
        log.exception("request failed")
        raise HTTPException(status_code=500, detail="The agent hit an error. Please try again.")


@app.post("/score/{session_id}", response_model=ScoreResponse, dependencies=[Depends(require_backend_secret)])
async def get_score(session_id: str):
    """
    Score the participant's session.
    Can only be called once per session.
    """
    validate_session(session_id)

    meta = get_session_meta(session_id)

    if meta.get("score_used") == "true":
        raise HTTPException(status_code=403, detail="You have already received your score.")

    try:
        from scorer import score_session
        score_result = score_session(session_id)

        # Mark score as used
        r.hset(f"session:{session_id}", "score_used", "true")

        # Kill sandbox after scoring — free up the slot
        kill_sandbox(session_id)

        return ScoreResponse(
            score=score_result["score"],
            feedback=score_result["feedback"],
            breakdown=score_result["breakdown"],
        )

    except Exception:
        log.exception("request failed")
        raise HTTPException(status_code=500, detail="Scoring failed. Please contact the organizer.")


@app.get("/status/{session_id}", response_model=StatusResponse, dependencies=[Depends(require_backend_secret)])
async def get_status(session_id: str):
    """
    Get current session status — time remaining, actions done, score used.
    Streamlit uses this to show the timer.
    """
    validate_session(session_id)

    meta = get_session_meta(session_id)

    return StatusResponse(
        session_id=session_id,
        participant_name=meta.get("participant_name", "Unknown"),
        action_count=int(meta.get("action_count", 0)),
        time_remaining_seconds=get_time_remaining(session_id),
        score_used=meta.get("score_used") == "true",
    )


@app.delete("/reset/{session_id}", dependencies=[Depends(require_admin_key)])
async def reset_session(session_id: str):
    """
    Admin endpoint to manually kill a session and free its sandbox.
    Use this if a participant abandons or needs a fresh start.
    """
    if not session_exists(session_id):
        raise HTTPException(status_code=404, detail="Session not found.")

    kill_sandbox(session_id)
    return {"message": f"Session {session_id} has been reset successfully."}


@app.get("/health")
async def health_check():
    """Quick health check to confirm API is running."""
    try:
        r.ping()
        redis_status = "connected"
    except Exception:
        redis_status = "disconnected"

    return {
        "status": "running",
        "redis": redis_status,
    }


@app.get("/")
async def root():
    return {"message": "DS Competition API is live 🚀"}