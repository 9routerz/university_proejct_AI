<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
$hasSession = !empty($_SESSION['session_id']);
$name = $_SESSION['name'] ?? '';
$needsCode = ACCESS_CODE !== '';
page_header('AI Agent', 'agent.php', 'agent-page');
?>

<!-- 1. Join -->
<section id="join" class="join-wrap <?= $hasSession ? 'hidden' : '' ?>">
  <div class="join-card">
    <div class="join-side">
      <span class="eyebrow">AI Data Agent</span>
      <h2>Your private data lab is <span class="grad-text">one click away</span></h2>
      <p class="muted">When you begin, we create a secure sandbox with the dataset loaded as <code class="inline-code">df</code>. Then just tell the agent what you want.</p>
      <ul class="check-list" style="margin-top:20px">
        <li>60 minutes from the moment you start</li>
        <li>The agent writes and runs the code for you</li>
        <li>Charts appear right in the chat</li>
        <li>One final score at the end</li>
      </ul>
      <p style="margin-top:20px;font-size:14px"><a href="guide.php">First time? Read the guide →</a></p>
    </div>
    <form class="join-form" id="join-form">
      <h3>Begin your session</h3>
      <p class="muted" style="font-size:15px">Use your real name. It appears on your result.</p>
      <div class="field">
        <label for="name">Full name</label>
        <input class="input" id="name" placeholder="e.g. Ayesha Khan" maxlength="60" autocomplete="name" required>
      </div>
      <?php if ($needsCode): ?>
      <div class="field">
        <label for="code">Access code</label>
        <input class="input" id="code" type="password" placeholder="Given by the organizers" maxlength="100" autocomplete="off" required>
      </div>
      <?php endif; ?>
      <button id="join-btn" class="btn btn-primary" type="submit" style="width:100%;margin-top:6px">Begin session <span aria-hidden="true">→</span></button>
      <div id="join-error" class="error" role="alert"></div>
    </form>
  </div>
</section>

<!-- 2. Chat -->
<section id="chat" class="chat-app <?= $hasSession ? '' : 'hidden' ?>">
  <aside class="chat-side">
    <h4>Quick prompts</h4>
    <div class="side-chips" id="side-chips">
      <button class="chip" type="button">show the shape and column types</button>
      <button class="chip" type="button">show the first 10 rows</button>
      <button class="chip" type="button">give me summary statistics</button>
      <button class="chip" type="button">how many missing values are in each column?</button>
      <button class="chip" type="button">how many duplicate rows are there?</button>
      <button class="chip" type="button">show the unique values of City</button>
      <button class="chip" type="button">plot a histogram of Age</button>
    </div>
    <h4>Remember</h4>
    <p class="side-note">Be specific, fix one thing at a time, and check your results. You can only get your score once.</p>
  </aside>

  <div class="chat-main">
    <div class="chat-top">
      <div class="chat-who">
        <span class="avatar" id="avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($name, 0, 1)) ?: '?') ?></span>
        <div style="min-width:0">
          <span id="who"><?= htmlspecialchars($name) ?></span>
          <small>● Sandbox connected</small>
        </div>
      </div>
      <div class="chat-stats">
        <span class="stat actions">Actions <b id="actions">0</b></span>
        <span class="stat">Time <b class="timer" id="timer">--:--</b></span>
        <button class="btn btn-ok btn-sm" id="score-btn" type="button"><span class="long">Get my score</span><span class="short">Score</span></button>
      </div>
    </div>
    <div class="timer-bar"><div id="timer-bar"></div></div>
    <div id="messages" aria-live="polite"></div>
    <button class="scroll-down hidden" id="scroll-down" type="button" aria-label="Jump to the latest message">↓</button>
    <form class="composer" id="chat-form">
      <!-- Quick prompts for phones and tablets (the sidebar is hidden there) -->
      <div class="mobile-chips" id="mobile-chips" aria-label="Quick prompts">
        <button class="chip" type="button">show the first 10 rows</button>
        <button class="chip" type="button">give me summary statistics</button>
        <button class="chip" type="button">missing values per column</button>
        <button class="chip" type="button">how many duplicate rows?</button>
        <button class="chip" type="button">unique values of City</button>
        <button class="chip" type="button">plot a histogram of Age</button>
      </div>
      <div class="composer-tools">
        <label for="model-select"><?= str_replace('<svg', '<svg width="16" height="16"', icon('spark')) ?> AI model</label>
        <select id="model-select" class="model-select" aria-label="Choose the AI model">
          <option value="">Claude Sonnet 5.5 (default)</option>
        </select>
        <span class="model-note" id="model-note">Balanced and fast</span>
      </div>
      <div class="composer-inner">
        <textarea class="input" id="message" placeholder="Tell the agent what to do… e.g. show me the first 5 rows" maxlength="2000" rows="1"></textarea>
        <button id="send-btn" class="btn btn-primary" type="submit" aria-label="Send"><span class="label">Send</span> <?= str_replace('<svg', '<svg width="18" height="18"', icon('send')) ?></button>
      </div>
      <div class="composer-hint">Enter to send · Shift + Enter for a new line</div>
    </form>
  </div>
</section>

<!-- 3. Score -->
<section id="result" class="join-wrap hidden">
  <div class="score-card">
    <span class="eyebrow">Session complete</span>
    <h2>Your final score</h2>
    <div class="score-ring" id="score-ring"><div><span id="score-value">0</span></div></div>
    <div class="score-box"><h4>Breakdown</h4><div id="score-breakdown"></div></div>
    <div class="score-box"><h4>Feedback</h4><div id="score-feedback"></div></div>
    <a href="index.php" class="btn btn-ghost" style="margin-top:20px">Back to home</a>
  </div>
</section>

<script src="app.js?v=<?= filemtime(__DIR__ . '/app.js') ?>" data-has-session="<?= $hasSession ? '1' : '0' ?>"></script>
<?php page_footer(false); ?>
