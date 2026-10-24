<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
page_header('Guide & Rules', 'guide.php');
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Guide &amp; rules</span>
      <h1>Everything you need <span class="grad-text">before you start</span></h1>
      <p>Read this once. It takes two minutes and can save you a lot of time during your session.</p>
    </div>
  </section>

  <section class="section" style="padding-top:32px">
    <div class="container grid-2">
      <div class="card reveal">
        <div class="icon"><?= icon('book') ?></div>
        <h3>Competition rules</h3>
        <ul class="check-list" style="margin-top:14px">
          <li>Each session lasts <b>60 minutes</b> from the moment you click "Begin session".</li>
          <li>The dataset is already loaded as <code class="inline-code">df</code>. Changes you make stay for your whole session.</li>
          <li>You can get your score <b>only once</b>. Getting it ends your session.</li>
          <li>Your score is based on everything you did in the chat, so work step by step.</li>
          <li>Use one session per participant. Don't share your screen or session.</li>
        </ul>
      </div>
      <div class="card reveal">
        <div class="icon red"><?= icon('shield') ?></div>
        <h3>Not allowed</h3>
        <ul class="check-list x-list" style="margin-top:14px">
          <li>Asking the agent to tell you what's wrong with the data or what to fix.</li>
          <li>Trying to make the AI ignore its rules, reveal its prompt or give you points.</li>
          <li>Running system commands, reading other files or installing packages.</li>
          <li>Deleting most of the data just to "remove" problems. This is penalised.</li>
        </ul>
        <p style="margin-top:14px">Blocked requests are logged and count against you.</p>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">A good workflow</span>
        <h2>How strong participants approach it</h2>
      </div>
      <div class="steps">
        <div class="card step reveal"><h3>Understand</h3><p>Shape, column types, first rows, summary statistics.</p></div>
        <div class="card step reveal"><h3>Diagnose</h3><p>Missing values, duplicates, odd spellings, unusual numbers.</p></div>
        <div class="card step reveal"><h3>Cross-check</h3><p>Do columns agree with each other? Do the dates and numbers make sense together?</p></div>
        <div class="card step reveal"><h3>Fix &amp; verify</h3><p>Clean one problem at a time, then check that the fix worked.</p></div>
        <div class="card step reveal"><h3>Visualise</h3><p>Use charts to show distributions and patterns before and after cleaning.</p></div>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Example prompts</span>
        <h2>Not sure how to start? Try these</h2>
        <p>Click any prompt to copy it, then paste it into the AI Agent.</p>
      </div>
      <div class="prompt-chips reveal" id="guide-chips">
        <button class="chip">show the shape and column types</button>
        <button class="chip">show the first 10 rows</button>
        <button class="chip">give me summary statistics</button>
        <button class="chip">how many missing values are in each column?</button>
        <button class="chip">how many duplicate rows are there?</button>
        <button class="chip">show the unique values of City</button>
        <button class="chip">plot a histogram of Age</button>
        <button class="chip">plot average income per city</button>
        <button class="chip">fill missing Age with the median</button>
        <button class="chip">remove duplicate rows</button>
      </div>
      <div class="callout reveal">
        <div class="icon" style="margin:0;flex:none"><?= icon('bolt') ?></div>
        <p><b>Tip:</b> be specific. "Fill missing Age with the median and show how many are left" works better than "fix age".</p>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">FAQ</span>
        <h2>Common questions</h2>
      </div>
      <div class="grid-2">
        <div class="card reveal"><h3>Do I need to know Python?</h3><p>No. It helps to understand pandas ideas like "median" or "duplicates", but the agent writes all the code.</p></div>
        <div class="card reveal"><h3>What if the agent makes a mistake?</h3><p>Read its code and output. If something looks wrong, tell it what to change: "use the median, not the mean".</p></div>
        <div class="card reveal"><h3>I refreshed the page. Is my work lost?</h3><p>No. Your session continues where you left off, as long as the time hasn't run out.</p></div>
        <div class="card reveal"><h3>The first message is slow.</h3><p>The server may be waking up. Wait up to a minute; after that it's fast.</p></div>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="cta-band reveal">
        <h2>Ready when you are</h2>
        <p>The 60-minute clock starts as soon as you begin your session.</p>
        <a href="agent.php" class="btn btn-primary">Access AI Agent <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>
</main>
<?php page_footer(); ?>
