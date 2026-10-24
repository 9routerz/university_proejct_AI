<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
page_header('Home', 'index.php');
?>
<main>
  <!-- Hero -->
  <section class="hero">
    <div class="container hero-grid">
      <div>
        <span class="pill"><b>LIVE</b> Data Science Competition · Air University Karachi</span>
        <h1>Clean data by <span class="grad-text">talking</span>, not typing code.</h1>
        <p class="lead">
          Get a messy real-world dataset and an AI agent that writes and runs Python for you.
          Your job is to think like a data scientist: find the problems, ask the right questions, and fix the data.
        </p>
        <div class="hero-cta">
          <a href="agent.php" class="btn btn-primary">Access AI Agent <span aria-hidden="true">→</span></a>
          <a href="about.php" class="btn btn-ghost">How it works</a>
        </div>
        <div class="hero-meta">
          <div><strong>5,000+</strong>citizen records</div>
          <div><strong>60 min</strong>per participant</div>
          <div><strong>100</strong>points to win</div>
        </div>
      </div>
      <div class="terminal reveal" aria-label="Example conversation with the AI agent">
        <div class="terminal-bar"><i></i><i></i><i></i><span>ai-agent · sandbox</span></div>
        <div class="terminal-body" id="hero-terminal"></div>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="section">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Why it's different</span>
        <h2>A data science contest where your <span class="grad-text">thinking</span> is what counts</h2>
        <p>Nobody wins by typing faster. You win by spotting what's wrong with the data and knowing how to fix it.</p>
      </div>
      <div class="grid-3">
        <div class="card reveal"><div class="icon"><?= icon('chat') ?></div><h3>Plain-language commands</h3><p>Type "fill missing ages with the median" and the agent turns it into working pandas code.</p></div>
        <div class="card reveal"><div class="icon red"><?= icon('box') ?></div><h3>Your own private sandbox</h3><p>Every participant gets an isolated cloud computer with the dataset loaded. Nobody else can touch your data.</p></div>
        <div class="card reveal"><div class="icon green"><?= icon('chart') ?></div><h3>Charts on demand</h3><p>Ask for histograms, bar charts or scatter plots and see them right inside the chat.</p></div>
        <div class="card reveal"><div class="icon"><?= icon('shield') ?></div><h3>Fair and cheat-proof</h3><p>The agent only does what you ask. Requests to reveal answers or bypass the rules are blocked.</p></div>
        <div class="card reveal"><div class="icon red"><?= icon('clock') ?></div><h3>Timed sessions</h3><p>You have 60 minutes. A live timer and action counter keep you on track.</p></div>
        <div class="card reveal"><div class="icon green"><?= icon('trophy') ?></div><h3>Instant AI scoring</h3><p>When you're done, an AI judge reviews your whole session and gives a score out of 100 with feedback.</p></div>
      </div>
    </div>
  </section>

  <!-- Steps -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">How to play</span>
        <h2>Four steps from messy to clean</h2>
      </div>
      <div class="steps">
        <div class="card step reveal"><h3>Enter your name</h3><p>Open the AI Agent page and start your session. Your sandbox is ready in seconds.</p></div>
        <div class="card step reveal"><h3>Explore the data</h3><p>Ask for the shape, column types, summary statistics and missing values.</p></div>
        <div class="card step reveal"><h3>Find &amp; fix problems</h3><p>Look for duplicates, typos and values that don't make logical sense, then clean them.</p></div>
        <div class="card step reveal"><h3>Get your score</h3><p>Click "Get my score" once. The AI judge reviews everything you did.</p></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="cta-band reveal">
        <h2>Ready to <span class="grad-text">test your skills?</span></h2>
        <p>Read the guide first if it's your first time, then jump in. The clock starts when you begin your session.</p>
        <div class="hero-cta" style="justify-content:center">
          <a href="agent.php" class="btn btn-primary">Access AI Agent <span aria-hidden="true">→</span></a>
          <a href="guide.php" class="btn btn-ghost">Read the guide</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php page_footer(); ?>
