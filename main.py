import uuid
import time
import redis
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from config import validate_config, REDIS_URL, COMPETITION_TIME_LIMIT, SESSION_TTL
from tools import create_sandbox, kill_sandbox, get_session_meta
from agent import run_agent

# ── Validate env on startup ───────────────────────────────────────────────────
validate_config()

# ── Redis client ──────────────────────────────────────────────────────────────
r = redis.from_url(REDIS_URL, decode_responses=True)

# ── App ───────────────────────────────────────────────────────────────────────
app = FastAPI(
    title="DS Competition API",
    description="Backend for the Air University Data Science Competition",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


# ── Request / Response models ─────────────────────────────────────────────────
class StartRequest(BaseModel):
    participant_name: str


class StartResponse(BaseModel):
    session_id: str
    message: str
    time_limit_seconds: int


class ChatRequest(BaseModel):
    session_id: str
    message: str


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


def session_exists(session_id: str) -> bool:
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

@app.post("/start", response_model=StartResponse)
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

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to start session: {str(e)}")


@app.post("/chat", response_model=ChatResponse)
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

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Agent error: {str(e)}")


@app.post("/score/{session_id}", response_model=ScoreResponse)
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

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Scoring error: {str(e)}")


@app.get("/status/{session_id}", response_model=StatusResponse)
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


@app.delete("/reset/{session_id}")
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