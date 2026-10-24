import logging
import anthropic
from backend.config import (
    CLAUDE_API_KEY, CLAUDE_BASE_URL, CLAUDE_MODEL,
    CLAUDE_MODEL_FAST, CLAUDE_MODEL_CODE, CLAUDE_MODEL_CHAT, CLAUDE_MODEL_JUDGE,
    CLAUDE_FALLBACK_MODELS,
)

log = logging.getLogger("ds-api.llm")

# ── Claude client (talks to the 9router proxy) ────────────────────────────────
# One retry per model: if a model keeps failing we move on to the next one
# instead of waiting on the same model.
client = anthropic.Anthropic(
    api_key=CLAUDE_API_KEY,
    base_url=CLAUDE_BASE_URL,
    timeout=90.0,
    max_retries=1,
)

# Which model does which job
TASK_MODELS = {
    "intent": CLAUDE_MODEL_FAST,
    "code": CLAUDE_MODEL_CODE,
    "chat": CLAUDE_MODEL_CHAT,
    "judge": CLAUDE_MODEL_JUDGE,
}


def models_for(task: str, preferred: str | None = None) -> list[str]:
    """The chosen (or the task's own) model first, then the backups, without duplicates."""
    order = [preferred or TASK_MODELS.get(task, CLAUDE_MODEL_CODE), *CLAUDE_FALLBACK_MODELS, CLAUDE_MODEL]
    return list(dict.fromkeys(m for m in order if m))


def ask_claude(system: str, messages: list[dict], max_tokens: int = 4000, task: str = "code", model: str | None = None) -> str:
    """
    Send a system prompt + messages to Claude and return the text reply.
    messages: [{"role": "user" | "assistant", "content": "..."}]
    task: "intent" | "code" | "chat" | "judge" (picks the model; backups are tried if it fails)
    model: a model to try first instead of the task's default
    """
    return ask_claude_with_model(system, messages, max_tokens, task, model)[0]


def ask_claude_with_model(system: str, messages: list[dict], max_tokens: int = 4000, task: str = "code", model: str | None = None) -> tuple[str, str]:
    """Same as ask_claude, but also returns which model actually answered."""
    last_error = None
    for model in models_for(task, model):
        try:
            response = client.messages.create(
                model=model,
                max_tokens=max_tokens,
                system=system,
                messages=messages,
            )
        except (anthropic.APIStatusError, anthropic.APIConnectionError) as e:
            # Down, rate limited, unknown model on 9router, timeout: try the next model
            log.warning("model %s failed for task %s: %s", model, task, e)
            last_error = e
            continue

        text = "".join(block.text for block in response.content if block.type == "text").strip()
        if response.stop_reason == "refusal" or not text:
            log.warning("model %s gave no answer for task %s (stop_reason=%s)", model, task, response.stop_reason)
            continue

        log.info("task %s answered by %s", task, model)
        return text, model

    if last_error:
        raise last_error
    return "", ""
