import anthropic
from config import CLAUDE_API_KEY, CLAUDE_BASE_URL, CLAUDE_MODEL

# ── Claude client (talks to the 9router proxy on Railway) ─────────────────────
client = anthropic.Anthropic(
    api_key=CLAUDE_API_KEY,
    base_url=CLAUDE_BASE_URL,
    timeout=120.0,
)


def ask_claude(system: str, messages: list[dict], max_tokens: int = 4000) -> str:
    """
    Send a system prompt + messages to Claude and return the text reply.
    messages: [{"role": "user" | "assistant", "content": "..."}]
    """
    response = client.messages.create(
        model=CLAUDE_MODEL,
        max_tokens=max_tokens,
        system=system,
        messages=messages,
    )

    if response.stop_reason == "refusal":
        return ""

    return "".join(block.text for block in response.content if block.type == "text").strip()
