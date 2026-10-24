<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
$hasSession = !empty($_SESSION['session_id']);
$name = $_SESSION['name'] ?? '';
$needsCode = ACCESS_CODE !== '';

// Suggested questions shown above the message box
$suggestions = [
    ['Explore', 'blue', ['Show the first 10 rows', 'Show the shape and column types', 'Give me summary statistics']],
    ['Find problems', 'red', ['How many missing values are in each column?', 'How many duplicate rows are there?', 'Show the unique values of City']],
    ['Visualise', 'green', ['Plot a histogram of Age', 'Plot the number of people per City']],
    ['Learn', 'navy', ['When should I use median instead of mean?']],
];
page_header('AI Agent', 'agent.php', 'agent-page');
?>

<!-- Chat (always visible; blurred behind the name popup until a session starts) -->
<section id="chat" class="chat-app">
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
          <span id="who"><?= htmlspecialchars($name ?: 'Guest') ?></span>
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
      <!-- Suggested questions: open on the first visit, reopen with the Suggestions button -->
      <div class="suggest-panel hidden" id="suggest-panel" aria-label="Suggested questions">
        <div class="suggest-head">
          <span><?= str_replace('<svg', '<svg width="16" height="16"', icon('bulb')) ?> Not sure where to start? Tap a question</span>
          <button type="button" class="suggest-close" id="suggest-close" aria-label="Hide suggestions"><?= str_replace('<svg', '<svg width="16" height="16"', icon('close')) ?></button>
        </div>
        <div class="suggest-body">
          <?php foreach ($suggestions as [$group, $color, $items]): ?>
            <div class="suggest-group <?= $color ?>">
              <span class="suggest-label"><?= $group ?></span>
              <?php foreach ($items as $q): ?>
                <button type="button" class="suggest-btn"><?= htmlspecialchars($q) ?></button>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="composer-tools">
        <button type="button" class="tool-btn" id="suggest-toggle" aria-expanded="false" aria-controls="suggest-panel">
          <?= str_replace('<svg', '<svg width="16" height="16"', icon('bulb')) ?> <span>Suggestions</span>
        </button>
        <label for="model-select" class="model-label"><?= str_replace('<svg', '<svg width="16" height="16"', icon('spark')) ?> <span>AI model</span></label>
        <select id="model-select" class="model-select" aria-label="Choose the AI model">
          <option value="">Claude Sonnet 5.5 (default)</option>
        </select>
        <span class="model-note" id="model-note">Balanced and fast</span>
      </div>
      <div class="composer-inner">
        <textarea class="input" id="message" placeholder="Ask the agent… e.g. show me the first 5 rows" maxlength="2000" rows="1"></textarea>
        <button id="send-btn" class="btn btn-primary" type="submit" aria-label="Send"><span class="label">Send</span> <?= str_replace('<svg', '<svg width="18" height="18"', icon('send')) ?></button>
      </div>
      <div class="composer-hint">Enter to send · Shift + Enter for a new line</div>
    </form>
  </div>
</section>

<!-- Name popup over the chat -->
<div id="join" class="modal <?= $hasSession ? 'hidden' : '' ?>" role="dialog" aria-modal="true" aria-labelledby="join-title">
  <form class="modal-card" id="join-form">
    <div class="modal-icon"><?= str_replace('<svg', '<svg width="26" height="26"', icon('spark')) ?></div>
    <h2 id="join-title">Start your AI session</h2>
    <p class="muted">Your private sandbox is ready in seconds, with the dataset loaded as <code class="inline-code">df</code>.</p>
    <div class="field">
      <label for="name">Your full name</label>
      <input class="input" id="name" placeholder="e.g. Ayesha Khan" maxlength="60" autocomplete="name" required>
    </div>
    <?php if ($needsCode): ?>
    <div class="field">
      <label for="code">Access code</label>
      <input class="input" id="code" type="password" placeholder="Given by the organizers" maxlength="100" autocomplete="off" required>
    </div>
    <?php endif; ?>
    <button id="join-btn" class="btn btn-primary" type="submit">Begin session <span aria-hidden="true">→</span></button>
    <div id="join-error" class="error" role="alert"></div>
    <div class="modal-facts">
      <span><?= str_replace('<svg', '<svg width="15" height="15"', icon('clock')) ?> 60 minutes</span>
      <span><?= str_replace('<svg', '<svg width="15" height="15"', icon('shield')) ?> Private sandbox</span>
      <span><?= str_replace('<svg', '<svg width="15" height="15"', icon('trophy')) ?> One score</span>
    </div>
    <p class="modal-links"><a href="guide.php">Read the guide</a> · <a href="index.php">Back to home</a></p>
  </form>
</div>

<!-- Score -->
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
