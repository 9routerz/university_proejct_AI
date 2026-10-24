import re
import os
import json
import redis
from e2b_code_interpreter import Sandbox
from config import (
    E2B_API_KEY,
    REDIS_URL,
    SANDBOX_TIMEOUT,
    SANDBOX_IDLE_TTL,
    SANDBOX_LIFETIME,
    SESSION_TTL,
    CSV_PATH,
    BLOCKED_CODE_PATTERNS,
)


# ── Set E2B API key as env var (new E2B SDK requirement) ──────────────────────
os.environ["E2B_API_KEY"] = E2B_API_KEY

# ── Redis client ──────────────────────────────────────────────────────────────
def make_redis(url: str):
    """REDIS_URL=memory:// keeps everything in memory (local testing without Redis)."""
    if url.startswith("memory://"):
        import fakeredis
        return fakeredis.FakeRedis(decode_responses=True)
    return redis.from_url(url, decode_responses=True)


r = make_redis(REDIS_URL)


# ── Safety check ─────────────────────────────────────────────────────────────
def is_code_safe(code: str) -> tuple[bool, str]:
    """
    Check generated code against blocked patterns before sending to E2B.
    Returns (is_safe, reason).
    """
    code_lower = code.lower()
    for pattern in BLOCKED_CODE_PATTERNS:
        if pattern.lower() in code_lower:
            return False, f"Blocked pattern detected: `{pattern}`"
    return True, ""


# ── Sandbox lifecycle ─────────────────────────────────────────────────────────
def create_sandbox(session_id: str) -> str:
    """
    Spin up a new E2B sandbox, preload the CSV as `df`, and store sandbox ID in Redis.
    Returns the sandbox ID.
    """
    # E2B deletes a sandbox after 5 minutes by default, so keep it alive for the whole session
    sbx = Sandbox.create(timeout=SANDBOX_LIFETIME)

    # Upload the CSV into the sandbox
    with open(CSV_PATH, "rb") as f:
        sbx.files.write("/home/user/dataset.csv", f)

    # Pre-execute setup code so df is always ready
    setup_code = """
import pandas as pd
import matplotlib
matplotlib.use('Agg')  # non-interactive backend for rendering graphs
import matplotlib.pyplot as plt
import numpy as np
import io, base64, warnings
warnings.filterwarnings('ignore')

df = pd.read_csv('/home/user/dataset.csv')
print(f"Dataset loaded: {df.shape[0]} rows, {df.shape[1]} columns")
print(df.dtypes)
"""
    sbx.run_code(setup_code)

    # Persist sandbox ID to Redis
    r.hset(f"session:{session_id}", mapping={
        "sandbox_id": sbx.sandbox_id,
        "score_used": "false",
        "action_count": "0",
    })
    r.expire(f"session:{session_id}", SESSION_TTL)

    return sbx.sandbox_id


def get_sandbox(session_id: str) -> Sandbox | None:
    """
    Reconnect to an existing sandbox from Redis.
    Returns None if session doesn't exist.
    """
    sandbox_id = r.hget(f"session:{session_id}", "sandbox_id")
    if not sandbox_id:
        return None
    return Sandbox.connect(sandbox_id)


def kill_sandbox(session_id: str):
    """
    Kill the sandbox and clean up Redis keys for this session.
    """
    try:
        sbx = get_sandbox(session_id)
        if sbx:
            sbx.kill()
    except Exception:
        pass  # sandbox may already be dead
    finally:
        r.delete(f"session:{session_id}")
        r.delete(f"session:{session_id}:history")


# ── Code execution ────────────────────────────────────────────────────────────
def execute_code(session_id: str, code: str) -> dict:
    """
    Execute code in the participant's sandbox.
    Returns dict with keys: output, error, image_base64 (if a plot was generated).
    """
    # Safety check first
    safe, reason = is_code_safe(code)
    if not safe:
        return {
            "output": None,
            "error": f"🚫 Blocked: {reason}",
            "image_base64": None,
        }

    sbx = get_sandbox(session_id)
    if not sbx:
        return {
            "output": None,
            "error": "Session not found. Please start a new session.",
            "image_base64": None,
        }

    # Wrap code to capture matplotlib figures as base64
    wrapped_code = f"""
{code}

# Auto-capture any matplotlib figure
try:
    fig = plt.gcf()
    if fig.get_axes():
        buf = io.BytesIO()
        fig.savefig(buf, format='png', bbox_inches='tight', dpi=120)
        buf.seek(0)
        img_b64 = base64.b64encode(buf.read()).decode('utf-8')
        print(f"__PLOT__{{img_b64}}__PLOT__")
        plt.close('all')
except Exception:
    pass
"""

    try:
        result = sbx.run_code(wrapped_code, timeout=SANDBOX_TIMEOUT)

        # Parse output
        full_output = ""
        image_base64 = None

        # stdout can arrive in chunks that mix printed text with the plot marker,
        # so join it first, then cut the base64 image out and keep the rest
        full_output = "".join(result.logs.stdout)
        plot = re.search(r"__PLOT__(.*?)__PLOT__", full_output, flags=re.S)
        if plot:
            image_base64 = plot.group(1).strip()
            full_output = full_output[:plot.start()] + full_output[plot.end():]

        error_output = "".join(result.logs.stderr) if result.logs.stderr else None

        # Increment action count in Redis
        r.hincrby(f"session:{session_id}", "action_count", 1)

        return {
            "output": full_output.strip() or "✅ Code executed successfully (no output)",
            "error": error_output,
            "image_base64": image_base64,
        }

    except TimeoutError:
        return {
            "output": None,
            "error": f"⏱️ Code timed out after {SANDBOX_TIMEOUT} seconds. Avoid infinite loops!",
            "image_base64": None,
        }
    except Exception as e:
        return {
            "output": None,
            "error": f"Execution error: {str(e)}",
            "image_base64": None,
        }


# ── Session history ───────────────────────────────────────────────────────────
def append_to_history(session_id: str, role: str, content: str):
    """Append a message to the session history in Redis."""
    history_key = f"session:{session_id}:history"
    entry = json.dumps({"role": role, "content": content})
    r.rpush(history_key, entry)
    r.expire(history_key, SESSION_TTL)


def get_history(session_id: str) -> list[dict]:
    """Retrieve full session history from Redis."""
    history_key = f"session:{session_id}:history"
    raw = r.lrange(history_key, 0, -1)
    return [json.loads(entry) for entry in raw]


def get_session_meta(session_id: str) -> dict:
    """Get session metadata (action count, score_used flag etc.)"""
    data = r.hgetall(f"session:{session_id}")
    return data if data else {}


def mark_score_used(session_id: str):
    """Lock scoring so it can only be called once."""
    r.hset(f"session:{session_id}", "score_used", "true")


def is_score_used(session_id: str) -> bool:
    """Check if participant already scored their session."""
    return r.hget(f"session:{session_id}", "score_used") == "true"