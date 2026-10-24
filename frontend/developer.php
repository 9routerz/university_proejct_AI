<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/partials/layout.php';
page_header('Developer', 'developer.php');
?>
<main>
  <!-- Profile hero -->
  <section class="profile-hero">
    <div class="container profile-grid">
      <div class="profile-photo">
        <div class="photo-ring">
          <img src="assets/developer.jpg" alt="Zain Ul Abideen" width="600" height="850">
        </div>
        <span class="photo-chip chip-top"><?= icon('spark') ?> AI Developer</span>
        <span class="photo-chip chip-bottom"><span class="live-dot"></span> Open to projects</span>
      </div>

      <div class="profile-intro">
        <span class="profile-kicker">Developer &amp; Creator of this platform</span>
        <h1>Zain Ul Abideen</h1>
        <p class="profile-title">Full-Stack &amp; AI Developer</p>
        <p class="profile-lead">
          I build intelligent web applications that connect large language models to real tools,
          real data and real users, with security designed in from the first line of code.
        </p>
        <div class="profile-meta">
          <span><?= icon('school') ?> Air University Karachi</span>
          <span><?= icon('code') ?> Computer Science</span>
          <span><?= icon('pin') ?> Karachi, Pakistan</span>
        </div>
        <div class="hero-cta">
          <a href="agent.php" class="btn btn-light">See my work live <?= str_replace('<svg', '<svg width="18" height="18"', icon('arrow')) ?></a>
          <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="btn btn-outline-light">Work with Novatix Solution ↗</a>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="stat-strip">
        <div><strong>1</strong><span>AI agent in production</span></div>
        <div><strong>5</strong><span>Pages designed &amp; built</span></div>
        <div><strong>9+</strong><span>Technologies integrated</span></div>
        <div><strong>6</strong><span>Security layers</span></div>
      </div>
    </div>
  </section>

  <!-- About + Education -->
  <section class="section">
    <div class="container about-grid">
      <div class="card about-card reveal">
        <span class="eyebrow">About me</span>
        <h2>Turning ideas into working AI products</h2>
        <p class="muted">
          I'm a Computer Science student at Air University Karachi who loves building things that actually work end to end.
          For this competition I designed the whole system: the AI agent that turns plain English into Python,
          the secure sandboxes that run that code, the backend that ties it together, and this website.
        </p>
        <p class="muted">
          My focus is simple: make powerful technology easy to use, fast, and safe.
        </p>
        <ul class="check-list">
          <li>Designed an AI agent that writes and runs data analysis code on demand</li>
          <li>Built a secure API and a modern, responsive web interface</li>
          <li>Deployed on cloud infrastructure: Render, Hostinger, E2B</li>
        </ul>
      </div>

      <div class="card edu-card reveal">
        <span class="eyebrow">Education</span>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot"><?= icon('school') ?></div>
            <div>
              <span class="tl-date">2026 – Present</span>
              <h3>BS Computer Science</h3>
              <p class="muted">Air University Karachi · 1st Semester</p>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot green"><?= icon('trophy') ?></div>
            <div>
              <span class="tl-date">2026</span>
              <h3>AU Data Science Competition</h3>
              <p class="muted">Developer of the AI-powered competition platform</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Skills -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Tech stack</span>
        <h2>Tools I work with</h2>
      </div>
      <div class="skill-grid">
        <div class="card skill-card reveal">
          <div class="icon"><?= icon('spark') ?></div>
          <h3>AI &amp; Data</h3>
          <div class="tags"><span class="tag">Claude API</span><span class="tag">AI Agents</span><span class="tag">LangGraph</span><span class="tag">Prompt Engineering</span><span class="tag">Pandas</span><span class="tag">Matplotlib</span></div>
        </div>
        <div class="card skill-card reveal">
          <div class="icon red"><?= icon('layers') ?></div>
          <h3>Backend</h3>
          <div class="tags"><span class="tag">Python</span><span class="tag">FastAPI</span><span class="tag">REST APIs</span><span class="tag">Redis</span><span class="tag">E2B Sandboxes</span></div>
        </div>
        <div class="card skill-card reveal">
          <div class="icon green"><?= icon('globe') ?></div>
          <h3>Frontend</h3>
          <div class="tags"><span class="tag">PHP</span><span class="tag">HTML5</span><span class="tag">CSS3</span><span class="tag">JavaScript</span><span class="tag">Responsive Design</span></div>
        </div>
        <div class="card skill-card reveal">
          <div class="icon"><?= icon('shield') ?></div>
          <h3>Security &amp; Cloud</h3>
          <div class="tags"><span class="tag">API Auth</span><span class="tag">CSRF</span><span class="tag">CSP</span><span class="tag">Render</span><span class="tag">Hostinger</span><span class="tag">Git</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured project -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Featured project</span>
        <h2>AU DS Competition: AI Data Agent</h2>
      </div>
      <div class="project-card reveal">
        <div class="project-body">
          <span class="project-badge"><span class="live-dot"></span> Live platform</span>
          <h3>Data science by conversation</h3>
          <p class="muted">
            A competition platform where students clean and analyse a messy dataset by chatting with an AI agent.
            Each participant gets a private cloud sandbox; the agent writes pandas code, runs it, and shows tables and charts instantly.
            An AI judge scores every session out of 100.
          </p>
          <div class="project-points">
            <div><?= icon('check') ?> Natural-language to Python</div>
            <div><?= icon('check') ?> Isolated sandbox per user</div>
            <div><?= icon('check') ?> Live charts in chat</div>
            <div><?= icon('check') ?> Automatic AI scoring</div>
          </div>
          <div class="tags" style="margin-bottom:22px"><span class="tag">Claude</span><span class="tag">FastAPI</span><span class="tag">E2B</span><span class="tag">Redis</span><span class="tag">PHP</span></div>
          <div class="hero-cta">
            <a href="agent.php" class="btn btn-primary">Open the AI Agent</a>
            <a href="about.php" class="btn btn-ghost">How it works</a>
          </div>
        </div>
        <div class="project-preview" aria-hidden="true">
          <div class="pv-window">
            <div class="pv-bar"><i></i><i></i><i></i></div>
            <div class="pv-msg">fill missing Age with the median</div>
            <div class="pv-code"><b>PYTHON</b>df['Age'] = df['Age'].fillna(df['Age'].median())</div>
            <div class="pv-out"><b>OUTPUT</b>Filled 120 values · Age now has 0 missing</div>
            <div class="pv-chart"><span class="h1"></span><span class="h2"></span><span class="h3"></span><span class="h4"></span><span class="h5"></span><span class="h6"></span><span class="h7"></span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Novatix -->
  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="novatix-card reveal">
        <div>
          <span class="eyebrow">Created by</span>
          <h3><a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">Novatix Solution</a></h3>
          <p class="muted" style="margin:0">Need a website, an AI agent or custom software? Visit <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">novatixsolution.com</a> to start your project.</p>
        </div>
        <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="btn btn-primary">Visit novatixsolution.com <span aria-hidden="true">↗</span></a>
      </div>
    </div>
  </section>
</main>
<?php page_footer(); ?>
