const $ = (id) => document.getElementById(id);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const HAS_SESSION = document.currentScript.dataset.hasSession === '1';
let secondsLeft = 0;
let timerHandle = null;

async function api(action, body) {
  const res = await fetch('api.php?action=' + action, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
    body: JSON.stringify(body || {}),
  });
  const data = await res.json().catch(() => ({ detail: 'Bad reply from server.' }));
  if (!res.ok) throw new Error(data.detail || 'Request failed.');
  return data;
}

function show(id) {
  ['join', 'chat', 'result'].forEach((s) => $(s).classList.toggle('hidden', s !== id));
}

// Safe mini-markdown: escape everything, then add code blocks, inline code and bold.
function render(text) {
  const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const parts = String(text || '').split(/```(?:\w+)?\n?([\s\S]*?)```/g);
  return parts.map((part, i) => {
    if (i % 2 === 1) return '<pre><code>' + esc(part.trimEnd()) + '</code></pre>';
    return esc(part.replace(/^\n+|\n+$/g, ''))
      .replace(/`([^`]+)`/g, '<code>$1</code>')
      .replace(/\*\*([^*]+)\*\*/g, '<b>$1</b>')
      .replace(/\n/g, '<br>');
  }).join('');
}

function addMessage(role, text, imageB64) {
  const div = document.createElement('div');
  div.className = 'msg ' + role;
  div.innerHTML = '<div class="role">' + (role === 'user' ? 'You' : 'Agent') + '</div>' + render(text);
  if (imageB64) {
    const img = document.createElement('img');
    img.src = 'data:image/png;base64,' + imageB64;
    img.alt = 'Plot';
    div.appendChild(img);
  }
  $('messages').appendChild(div);
  $('messages').scrollTop = $('messages').scrollHeight;
  return div;
}

function startTimer(seconds) {
  secondsLeft = seconds;
  clearInterval(timerHandle);
  const tick = () => {
    const m = Math.floor(secondsLeft / 60), s = secondsLeft % 60;
    $('timer').textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    $('timer').classList.toggle('low', secondsLeft <= 300);
    if (secondsLeft <= 0) {
      clearInterval(timerHandle);
      $('send-btn').disabled = true;
      $('message').disabled = true;
      addMessage('assistant', '⏰ Time is up! Click "Get my score" to see your result.');
    }
    secondsLeft = Math.max(0, secondsLeft - 1);
  };
  tick();
  timerHandle = setInterval(tick, 1000);
}

async function refreshStatus() {
  try {
    const st = await api('status');
    $('actions').textContent = st.action_count;
    $('who').textContent = st.participant_name;
    startTimer(st.time_remaining_seconds);
    return true;
  } catch (e) {
    return false;
  }
}

$('join-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  $('join-error').textContent = '';
  $('join-btn').disabled = true;
  $('join-btn').textContent = 'Preparing your sandbox... (can take up to a minute)';
  try {
    const name = $('name').value.trim();
    const code = $('code') ? $('code').value : '';
    const data = await api('start', { name, code });
    $('who').textContent = name;
    show('chat');
    addMessage('assistant', data.message);
    startTimer(data.time_limit_seconds);
  } catch (err) {
    $('join-error').textContent = err.message;
  } finally {
    $('join-btn').disabled = false;
    $('join-btn').textContent = 'Begin session';
  }
});

$('chat-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const text = $('message').value.trim();
  if (!text) return;
  $('message').value = '';
  addMessage('user', text);
  const pending = addMessage('assistant', '');
  pending.querySelector('.role').insertAdjacentHTML('afterend', '<span class="typing">Running...</span>');
  $('send-btn').disabled = true;
  try {
    const data = await api('chat', { message: text });
    pending.remove();
    addMessage('assistant', data.response, data.image_base64);
    secondsLeft = data.time_remaining_seconds;
    $('actions').textContent = Number($('actions').textContent) + (data.intent === 'code' ? 1 : 0);
  } catch (err) {
    pending.remove();
    addMessage('assistant', '⚠️ ' + err.message);
  } finally {
    if (secondsLeft > 0) $('send-btn').disabled = false;
    $('message').focus();
  }
});

// Enter sends, Shift+Enter makes a new line
$('message').addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); $('chat-form').requestSubmit(); }
});

$('score-btn').addEventListener('click', async () => {
  if (!confirm('You can only get your score once and your session will end. Continue?')) return;
  $('score-btn').disabled = true;
  $('score-btn').textContent = 'Evaluating...';
  try {
    const data = await api('score');
    clearInterval(timerHandle);
    $('score-value').textContent = data.score + '/100';
    $('score-breakdown').textContent = data.breakdown;
    $('score-feedback').textContent = data.feedback;
    show('result');
  } catch (err) {
    addMessage('assistant', '⚠️ ' + err.message);
    $('score-btn').disabled = false;
    $('score-btn').textContent = 'Get my score';
  }
});

// Page reload: resume the running session
if (HAS_SESSION) {
  refreshStatus().then((ok) => {
    if (ok) addMessage('assistant', 'Welcome back! Your session is still running. The dataset is loaded as `df`.');
    else { show('join'); }
  });
}
