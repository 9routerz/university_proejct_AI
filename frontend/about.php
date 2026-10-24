<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
page_header('About the Project', 'about.php');
?>
<main>
  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">About the project</span>
      <h1>Data science <span class="grad-text">through conversation</span></h1>
      <p>An AI-powered competition platform where students clean and analyse a messy dataset by giving instructions in plain English instead of writing code.</p>
    </div>
  </section>

  <!-- Idea -->
  <section class="section" style="padding-top:32px">
    <div class="container grid-2">
      <div class="reveal">
        <span class="eyebrow">The idea</span>
        <h2>Test how you think, not how fast you type</h2>
        <p class="muted">In real data science, most of the work is understanding the data: spotting missing values, duplicates, typos and records that simply can't be true. Writing the code is the easy part.</p>
        <p class="muted">This platform removes the coding barrier. Participants describe what they want, and an AI agent writes and runs the Python code for them. Everyone can take part, even first-semester students who are still learning pandas.</p>
      </div>
      <div class="card reveal">
        <h3>What a participant can ask</h3>
        <ul class="check-list" style="margin-top:14px">
          <li>"Give me the stats of this dataset" → runs <code class="inline-code">df.describe()</code></li>
          <li>"Fill the missing values in Age with the median"</li>
          <li>"Remove duplicate rows and show the new row count"</li>
          <li>"Plot a histogram of household income"</li>
          <li>"What's the difference between mean and median?"</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Architecture -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">How it works</span>
        <h2>What happens when you send a message</h2>
        <p>Your message passes through four parts. The AI never runs code on our server; every command runs inside your own private sandbox.</p>
      </div>
      <div class="flow reveal">
        <div class="flow-node"><div class="icon" style="margin:0 auto 10px"><?= icon('chat') ?></div><strong>You</strong><span>Type a request in plain English</span></div>
        <div class="flow-arrow">→</div>
        <div class="flow-node hl"><div class="icon" style="margin:0 auto 10px"><?= icon('spark') ?></div><strong>AI Agent (Claude)</strong><span>Understands the request and writes pandas code</span></div>
        <div class="flow-arrow">→</div>
        <div class="flow-node"><div class="icon red" style="margin:0 auto 10px"><?= icon('box') ?></div><strong>E2B Sandbox</strong><span>Runs the code on your private copy of the data</span></div>
        <div class="flow-arrow">→</div>
        <div class="flow-node"><div class="icon green" style="margin:0 auto 10px"><?= icon('chart') ?></div><strong>Result</strong><span>Code, output and charts appear in the chat</span></div>
      </div>
    </div>
  </section>

  <!-- Dataset -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">The dataset</span>
        <h2>Synthetic citizen registry records</h2>
        <p>A NADRA-style dataset with thousands of fictional citizens from Karachi, Lahore and Islamabad. All names and numbers are made up. It has been deliberately damaged: finding out <em>how</em> is your challenge.</p>
      </div>
      <div class="table-wrap reveal">
        <table>
          <thead><tr><th>Column</th><th>What it holds</th></tr></thead>
          <tbody>
            <tr><td><code>Citizen_ID</code></td><td>Record number</td></tr>
            <tr><td><code>CNIC</code> · <code>Father_CNIC</code></td><td>13-digit national ID of the citizen and their father</td></tr>
            <tr><td><code>Full_Name</code> · <code>Gender</code></td><td>Name and gender (M / F)</td></tr>
            <tr><td><code>DOB</code> · <code>Age</code></td><td>Date of birth and age in years</td></tr>
            <tr><td><code>CNIC_Registration_Date</code></td><td>When the ID card was issued</td></tr>
            <tr><td><code>City</code> · <code>Town_Area</code></td><td>Where the citizen lives</td></tr>
            <tr><td><code>Household_Income</code></td><td>Monthly income in PKR</td></tr>
            <tr><td><code>Marital_Status</code> · <code>Children_Count</code></td><td>Family details</td></tr>
          </tbody>
        </table>
      </div>
      <div class="callout reveal">
        <div class="icon" style="margin:0;flex:none"><?= icon('bolt') ?></div>
        <p><b>Hint:</b> some problems are easy to see (missing values, typos, duplicates). Others only show up when you check whether the columns agree with each other. Think about what must be true for a real person.</p>
      </div>
    </div>
  </section>

  <!-- Tech -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Technology</span>
        <h2>Built with modern AI infrastructure</h2>
      </div>
      <div class="grid-3">
        <div class="card reveal"><div class="icon"><?= icon('spark') ?></div><h3>Claude AI</h3><p>Anthropic's model understands each request, writes the pandas code, answers questions and judges the final session.</p></div>
        <div class="card reveal"><div class="icon red"><?= icon('box') ?></div><h3>E2B Sandboxes</h3><p>Secure, isolated cloud computers. AI-written code runs there, never on the main server.</p></div>
        <div class="card reveal"><div class="icon green"><?= icon('code') ?></div><h3>FastAPI + LangGraph</h3><p>The Python backend routes each message: code, question, score, or blocked.</p></div>
        <div class="card reveal"><div class="icon"><?= icon('data') ?></div><h3>Redis</h3><p>Keeps track of each session, its history, the timer and the action count.</p></div>
        <div class="card reveal"><div class="icon red"><?= icon('globe') ?></div><h3>PHP Frontend</h3><p>This website. It talks to the backend on the server side, so no secrets ever reach your browser.</p></div>
        <div class="card reveal"><div class="icon green"><?= icon('shield') ?></div><h3>Security first</h3><p>Secret-key API, CSRF protection, rate limits, strict content policy and blocked dangerous code.</p></div>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="cta-band reveal">
        <h2>See it in action</h2>
        <p>Start a session and send your first command. Your sandbox is ready in seconds.</p>
        <a href="agent.php" class="btn btn-primary">Access AI Agent <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>
</main>
<?php page_footer(); ?>
