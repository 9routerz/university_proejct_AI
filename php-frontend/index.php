<?php
require __DIR__ . '/bootstrap.php';
$hasSession = !empty($_SESSION['session_id']);
$name = $_SESSION['name'] ?? '';
$needsCode = ACCESS_CODE !== '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf']) ?>">
<title>DS Competition</title>
<style>
  :root {
    --bg: #0d1117; --panel: #161b22; --border: #30363d; --text: #e6edf3;
    --muted: #8b949e; --accent: #2f81f7; --accent-2: #1f6feb; --ok: #3fb950;
    --warn: #d29922; --danger: #f85149; --code: #0b0f14;
  }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--bg); color: var(--text);
         font: 15px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
  .hidden { display: none !important; }
  button { font: inherit; cursor: pointer; border: 0; border-radius: 8px;
           background: var(--accent-2); color: #fff; padding: 10px 18px; font-weight: 600; }
  button:hover { background: var(--accent); }
  button:disabled { opacity: .5; cursor: wait; }
  input, textarea { font: inherit; color: var(--text); background: var(--bg);
                    border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; width: 100%; }
  input:focus, textarea:focus { outline: 2px solid var(--accent); border-color: transparent; }

  /* Join screen */
  .center { min-height: 100vh; display: grid; place-items: center; padding: 16px; }
  .card { background: var(--panel); border: 1px solid var(--border); border-radius: 14px;
          padding: 28px; width: 100%; max-width: 440px; }
  .card h1 { margin: 0 0 6px; font-size: 24px; }
  .card p { margin: 0 0 20px; color: var(--muted); }
  .card button { width: 100%; margin-top: 12px; }
  .error { color: var(--danger); margin-top: 10px; min-height: 1.5em; }

  /* Chat screen */
  .app { display: flex; flex-direction: column; height: 100vh; }
  header { display: flex; gap: 16px; align-items: center; justify-content: space-between;
           padding: 12px 16px; border-bottom: 1px solid var(--border); background: var(--panel); flex-wrap: wrap; }
  header .who { font-weight: 600; }
  header .stats { display: flex; gap: 16px; color: var(--muted); align-items: center; flex-wrap: wrap; }
  .timer { font-variant-numeric: tabular-nums; color: var(--text); font-weight: 700; }
  .timer.low { color: var(--danger); }
  #messages { flex: 1; overflow-y: auto; padding: 16px; }
  .msg { max-width: 860px; margin: 0 auto 14px; padding: 12px 14px; border-radius: 12px;
         border: 1px solid var(--border); overflow-wrap: anywhere; }
  .msg.user { background: #1c2a3f; border-color: #24406b; }
  .msg.assistant { background: var(--panel); }
  .msg .role { font-size: 12px; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
  .msg pre { background: var(--code); border: 1px solid var(--border); border-radius: 8px;
             padding: 10px; overflow-x: auto; font-size: 13px; margin: 8px 0; }
  .msg code { font-family: ui-monospace, Consolas, monospace; }
  .msg img { max-width: 100%; border-radius: 8px; margin-top: 8px; background: #fff; }
  .typing { color: var(--muted); font-style: italic; }
  form#chat-form { display: flex; gap: 10px; padding: 12px 16px; border-top: 1px solid var(--border); background: var(--panel); }
  form#chat-form textarea { resize: none; height: 46px; }
  .score-btn { background: var(--ok); color: #04210c; }
  .score-btn:hover { background: #56d364; }

  /* Score screen */
  .big-score { font-size: 64px; font-weight: 800; margin: 8px 0; color: var(--ok); }
  .score-box { text-align: left; background: var(--bg); border: 1px solid var(--border);
               border-radius: 10px; padding: 12px; margin-top: 12px; white-space: pre-wrap; }

  @media (max-width: 600px) {
    form#chat-form { flex-direction: column; }
  }
</style>
</head>
<body>

<!-- 1. Join -->
<section id="join" class="center <?= $hasSession ? 'hidden' : '' ?>">
  <div class="card">
    <h1>Data Science Competition</h1>
    <p>Clean and analyse the dataset by chatting with the AI agent. You don't need to write code.</p>
    <form id="join-form">
      <input id="name" placeholder="Enter your name" maxlength="60" autocomplete="name" required>
      <?php if ($needsCode): ?>
      <input id="code" type="password" placeholder="Access code" maxlength="100" autocomplete="off" required style="margin-top:10px">
      <?php endif; ?>
      <button id="join-btn" type="submit">Begin session</button>
    </form>
    <div id="join-error" class="error"></div>
  </div>
</section>

<!-- 2. Chat -->
<section id="chat" class="app <?= $hasSession ? '' : 'hidden' ?>">
  <header>
    <div class="who" id="who"><?= htmlspecialchars($name) ?></div>
    <div class="stats">
      <span>Actions: <b id="actions">0</b></span>
      <span>Time left: <span class="timer" id="timer">--:--</span></span>
      <button class="score-btn" id="score-btn" type="button">Get my score</button>
    </div>
  </header>
  <div id="messages"></div>
  <form id="chat-form">
    <textarea id="message" placeholder="e.g. show me the first 5 rows" maxlength="2000"></textarea>
    <button id="send-btn" type="submit">Send</button>
  </form>
</section>

<!-- 3. Score -->
<section id="result" class="center hidden">
  <div class="card" style="text-align:center; max-width:560px">
    <h1>Your final score</h1>
    <div class="big-score" id="score-value">0</div>
    <div class="score-box" id="score-breakdown"></div>
    <div class="score-box" id="score-feedback"></div>
  </div>
</section>

<script src="app.js" data-has-session="<?= $hasSession ? '1' : '0' ?>"></script>
</body>
</html>
