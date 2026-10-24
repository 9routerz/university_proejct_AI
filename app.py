import streamlit as st
import requests
import base64

# ── Config ────────────────────────────────────────────────────────────────────
API_URL = st.secrets.get("API_URL", "http://127.0.0.1:8000").rstrip("/")
HEADERS = {"X-Backend-Secret": st.secrets.get("BACKEND_SECRET", "")}

# ── Page config ───────────────────────────────────────────────────────────────
st.set_page_config(
    page_title="DS Arena — Air University",
    page_icon="⚡",
    layout="wide",
    initial_sidebar_state="collapsed",
)

# ── Custom CSS ────────────────────────────────────────────────────────────────
st.markdown("""
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap');

*, *::before, *::after { box-sizing: border-box; }

html, body, .stApp {
    background-color: #080C14 !important;
    color: #C8D8F0 !important;
    font-family: 'Space Grotesk', sans-serif !important;
}

#MainMenu, footer, header { visibility: hidden; }
.block-container { padding: 1.5rem 2.5rem !important; max-width: 900px !important; }

/* ── Hero ── */
.ds-hero {
    border-bottom: 1px solid #1E2D45;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}
.ds-hero-eyebrow {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.68rem;
    color: #3B7DD8;
    letter-spacing: 0.15em;
    margin-bottom: 0.4rem;
}
.ds-hero-title {
    font-size: 2rem;
    font-weight: 700;
    color: #E8F0FF;
    line-height: 1.1;
    margin-bottom: 0.3rem;
}
.ds-hero-title span { color: #3B7DD8; }
.ds-hero-sub { font-size: 0.88rem; color: #5A7CA8; }

/* ── Status bar ── */
.status-bar {
    display: flex;
    gap: 2rem;
    align-items: center;
    background: #0D1520;
    border: 1px solid #1E2D45;
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    margin-bottom: 1.5rem;
    font-family: 'JetBrains Mono', monospace;
}
.status-item { display: flex; flex-direction: column; gap: 2px; }
.status-label { color: #3B6494; font-size: 0.65rem; letter-spacing: 0.08em; }
.status-value { color: #7EB8F7; font-weight: 500; font-size: 0.82rem; }
.status-value.danger { color: #F76E6E; }
.status-value.success { color: #4FD18B; }
.status-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #4FD18B;
    box-shadow: 0 0 5px #4FD18B;
    display: inline-block;
    margin-right: 5px;
    animation: pulse 2s infinite;
}
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

/* ── Streamlit chat messages ── */
[data-testid="stChatMessage"] {
    background: transparent !important;
    border: none !important;
    padding: 0.2rem 0 !important;
}
[data-testid="stChatMessageContent"] {
    background: #0D1A2A !important;
    border: 1px solid #1A2D45 !important;
    border-radius: 12px !important;
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 0.85rem !important;
    color: #8BAFD4 !important;
    padding: 0.75rem 1rem !important;
}
[data-testid="stChatMessage"]:has([data-testid="stChatMessageAvatarUser"]) [data-testid="stChatMessageContent"] {
    background: #152238 !important;
    border-color: #1E3A5F !important;
    color: #C0D8F8 !important;
    font-family: 'Space Grotesk', sans-serif !important;
    font-size: 0.92rem !important;
}
[data-testid="stChatMessageAvatarUser"],
[data-testid="stChatMessageAvatarAssistant"] {
    background: #0D1520 !important;
    border: 1px solid #1E2D45 !important;
}

/* ── Chat input ── */
[data-testid="stChatInput"] {
    background: #0D1520 !important;
    border: 1px solid #1E3050 !important;
    border-radius: 24px !important;
}
[data-testid="stChatInput"] textarea {
    background: transparent !important;
    color: #C8D8F0 !important;
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 0.88rem !important;
}
[data-testid="stChatInput"] textarea::placeholder { color: #2A4A6A !important; }
[data-testid="stChatInput"] button {
    color: #3B7DD8 !important;
}

/* ── Buttons ── */
.stButton button {
    background: #0F2040 !important;
    border: 1px solid #2A5080 !important;
    color: #7EB8F7 !important;
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 0.8rem !important;
    border-radius: 24px !important;
    padding: 0.55rem 1.4rem !important;
    transition: all 0.15s !important;
}
.stButton button:hover {
    background: #163060 !important;
    border-color: #3B7DD8 !important;
    color: #B8D8FF !important;
}
.score-btn button {
    background: #0A2010 !important;
    border: 1px solid #1A5030 !important;
    color: #4FD18B !important;
}
.score-btn button:hover {
    background: #0D3020 !important;
    border-color: #4FD18B !important;
}

/* ── Score card ── */
.score-card {
    background: linear-gradient(135deg, #0A1828 0%, #0D2040 100%);
    border: 1px solid #1E4080;
    border-radius: 14px;
    padding: 2.5rem;
    text-align: center;
    margin-top: 1rem;
}
.score-number {
    font-family: 'JetBrains Mono', monospace;
    font-size: 5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.3rem;
}
.score-label {
    font-size: 0.75rem;
    color: #3B6494;
    letter-spacing: 0.12em;
    font-family: 'JetBrains Mono', monospace;
    margin-bottom: 2rem;
}
.score-feedback {
    font-size: 0.92rem;
    color: #8BAFD4;
    line-height: 1.7;
    margin-bottom: 1.2rem;
    text-align: left;
}
.score-breakdown {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.78rem;
    color: #4FD18B;
    text-align: left;
    background: #060E18;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    border: 1px solid #0D2A18;
    line-height: 1.8;
}

/* ── Welcome ── */
.welcome-card {
    background: #0A1018;
    border: 1px solid #1A2840;
    border-radius: 14px;
    padding: 2.5rem;
    text-align: center;
    max-width: 460px;
    margin: 4rem auto 0;
}
.welcome-icon { font-size: 2.8rem; margin-bottom: 1rem; display: block; }
.welcome-title { font-size: 1.35rem; font-weight: 600; color: #E8F0FF; margin-bottom: 0.5rem; }
.welcome-sub { font-size: 0.86rem; color: #4A6A8A; margin-bottom: 2rem; line-height: 1.7; }

/* ── Name input ── */
.stTextInput input {
    background: #0D1520 !important;
    border: 1px solid #1E3050 !important;
    border-radius: 24px !important;
    color: #C8D8F0 !important;
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 0.9rem !important;
    padding: 0.65rem 1.2rem !important;
}
.stTextInput input:focus {
    border-color: #3B7DD8 !important;
    box-shadow: 0 0 0 2px rgba(59,125,216,0.12) !important;
}
.stTextInput input::placeholder { color: #2A4A6A !important; }
.stTextInput > label { display: none !important; }

/* ── Scrollbar ── */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: #080C14; }
::-webkit-scrollbar-thumb { background: #1A2D45; border-radius: 2px; }
</style>
""", unsafe_allow_html=True)


# ── Session state ─────────────────────────────────────────────────────────────
defaults = {
    "session_id": None,
    "participant_name": None,
    "messages": [],
    "score_result": None,
    "session_active": False,
}
for k, v in defaults.items():
    if k not in st.session_state:
        st.session_state[k] = v


# ── API helpers ───────────────────────────────────────────────────────────────
def api_start(name):
    r = requests.post(f"{API_URL}/start", json={"participant_name": name}, headers=HEADERS)
    return r.json()

def api_chat(session_id, message):
    r = requests.post(f"{API_URL}/chat", json={"session_id": session_id, "message": message}, headers=HEADERS)
    return r.json()

def api_score(session_id):
    r = requests.post(f"{API_URL}/score/{session_id}", headers=HEADERS)
    return r.json()

def api_status(session_id):
    r = requests.get(f"{API_URL}/status/{session_id}", headers=HEADERS)
    return r.json()

def format_time(seconds):
    m, s = divmod(seconds, 60)
    return f"{m:02d}:{s:02d}"

def render_chat_message(message):
    """Render a message with Streamlit's native chat container."""
    with st.chat_message(message["role"]):
        st.markdown(message.get("content", ""), unsafe_allow_html=False)
        image = message.get("image")
        if image:
            st.image(base64.b64decode(image), use_container_width=True)


# ── Hero ──────────────────────────────────────────────────────────────────────
st.markdown("""
<div class="ds-hero">
    <div class="ds-hero-eyebrow">AIR UNIVERSITY KARACHI · DATA SCIENCE COMPETITION</div>
    <div class="ds-hero-title">DS <span>Arena</span></div>
    <div class="ds-hero-sub">Clean the data. Explore the patterns. Prove your skills.</div>
</div>
""", unsafe_allow_html=True)


# ══════════════════════════════════════════════════════════════════════════════
# WELCOME SCREEN
# ══════════════════════════════════════════════════════════════════════════════
if not st.session_state.session_active:
    st.markdown("""
    <div class="welcome-card">
        <span class="welcome-icon">⚡</span>
        <div class="welcome-title">Ready to compete?</div>
        <div class="welcome-sub">
            A messy dataset will be loaded as <code>df</code>.<br>
            Clean it, explore it, visualize it.<br>
            When you're done — hit Score to get evaluated.<br><br>
            You have <strong>60 minutes.</strong>
        </div>
    </div>
    """, unsafe_allow_html=True)

    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        st.markdown("<br>", unsafe_allow_html=True)
        name = st.text_input("name", placeholder="Enter your name...", label_visibility="collapsed")
        if st.button("⚡  BEGIN SESSION", use_container_width=True):
            if name.strip():
                with st.spinner("Initializing your sandbox..."):
                    result = api_start(name.strip())
                if "session_id" in result:
                    st.session_state.session_id = result["session_id"]
                    st.session_state.participant_name = name.strip()
                    st.session_state.session_active = True
                    st.session_state.messages = [{
                        "role": "assistant",
                        "content": result["message"],
                        "image": None,
                    }]
                    st.rerun()
                else:
                    st.error(result.get("detail", "Failed to start session."))
            else:
                st.warning("Enter your name first.")


# ══════════════════════════════════════════════════════════════════════════════
# SCORE SCREEN
# ══════════════════════════════════════════════════════════════════════════════
elif st.session_state.score_result:
    result = st.session_state.score_result
    score = result.get("score", 0)
    color = "#4FD18B" if score >= 70 else "#F7C96E" if score >= 40 else "#F76E6E"
    st.markdown(f"""
    <div class="score-card">
        <div class="score-number" style="color:{color}">{score}</div>
        <div class="score-label">FINAL SCORE / 100</div>
        <div class="score-feedback">{result.get("feedback", "")}</div>
        <div class="score-breakdown">{result.get("breakdown", "")}</div>
    </div>
    """, unsafe_allow_html=True)


# ══════════════════════════════════════════════════════════════════════════════
# COMPETITION SCREEN
# ══════════════════════════════════════════════════════════════════════════════
else:
    # ── Status bar ──
    try:
        status = api_status(st.session_state.session_id)
        time_remaining = status.get("time_remaining_seconds", 0)
        action_count = status.get("action_count", 0)
        time_str = format_time(time_remaining)
        time_class = "danger" if time_remaining < 300 else "success"
    except Exception:
        time_str = "--:--"
        action_count = 0
        time_class = "success"

    st.markdown(f"""
    <div class="status-bar">
        <div class="status-item">
            <div class="status-label">PARTICIPANT</div>
            <div class="status-value"><span class="status-dot"></span>{st.session_state.participant_name}</div>
        </div>
        <div class="status-item">
            <div class="status-label">TIME LEFT</div>
            <div class="status-value {time_class}">{time_str}</div>
        </div>
        <div class="status-item">
            <div class="status-label">ACTIONS</div>
            <div class="status-value">{action_count}</div>
        </div>
    </div>
    """, unsafe_allow_html=True)

    # ── Render all messages with Streamlit's native chat UI ──
    for msg in st.session_state.messages:
        render_chat_message(msg)

    # ── Chat input ──
    user_input = st.chat_input("Type a message...")

    if user_input:
        # Show user message immediately
        st.session_state.messages.append({"role": "user", "content": user_input, "image": None})
        render_chat_message({"role": "user", "content": user_input})

        # Get agent response
        with st.chat_message("assistant"):
            with st.spinner("Running..."):
                result = api_chat(st.session_state.session_id, user_input)

            if "detail" in result:
                st.error(result["detail"])
                st.session_state.messages.pop()
            else:
                response = result.get("response", "")
                image = result.get("image_base64") or None
                st.markdown(response, unsafe_allow_html=False)
                if image:
                    img_data = base64.b64decode(image)
                    st.image(img_data, use_container_width=True)
                st.session_state.messages.append({
                    "role": "assistant",
                    "content": response,
                    "image": image,
                })

    # ── Score button at the very bottom ──
    st.markdown("<br><br>", unsafe_allow_html=True)
    col1, col2, col3 = st.columns([3, 2, 3])
    with col2:
        st.markdown('<div class="score-btn">', unsafe_allow_html=True)
        if st.button("⚡ Get My Score", use_container_width=True):
            with st.spinner("Evaluating your session..."):
                result = api_score(st.session_state.session_id)
            if "detail" in result:
                st.error(result["detail"])
            else:
                st.session_state.score_result = result
                st.rerun()
        st.markdown('</div>', unsafe_allow_html=True)
