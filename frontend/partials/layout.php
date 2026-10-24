<?php
// Shared page header (nav) and footer for every page.

const SITE_NAME = 'AU DS Competition';
const NAV = [
    'index.php'     => 'Home',
    'about.php'     => 'About Project',
    'guide.php'     => 'Guide & Rules',
    'developer.php' => 'Developer',
];

// Simple line icons (stroke uses currentColor)
function icon($name) {
    $paths = [
        'chat'    => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/>',
        'code'    => '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/>',
        'shield'  => '<path d="M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'chart'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'trophy'  => '<path d="M8 4h8v5a4 4 0 0 1-8 0V4ZM8 6H4a3 3 0 0 0 4 4M16 6h4a3 3 0 0 1-4 4M12 13v4M8 21h8M9 17h6"/>',
        'box'     => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'spark'   => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
        'data'    => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
        'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'school'  => '<path d="m2 9 10-5 10 5-10 5L2 9Z"/><path d="M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>',
        'book'    => '<path d="M4 5a2 2 0 0 1 2-2h14v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h14"/>',
        'pin'     => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
        'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9S14.5 18.3 12 21c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3Z"/>',
        'send'    => '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'layers'  => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'check'   => '<path d="m5 12 5 5 9-10"/>',
        'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'bolt'    =>'<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

function page_header($title, $active, $bodyClass = '') {
    $t = htmlspecialchars($title);
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf']) ?>">
<meta name="description" content="AI-powered data science competition at Air University Karachi. Clean and analyse data by chatting with an AI agent.">
<title><?= $t ?> · <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23f5b83d'/%3E%3Ctext x='16' y='22' font-size='16' text-anchor='middle' font-family='Arial' font-weight='700' fill='%230a1020'%3EAI%3C/text%3E%3C/svg%3E">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<header class="nav">
  <div class="nav-inner">
    <a href="index.php" class="brand">
      <span class="brand-mark">AI</span>
      <span class="brand-text">AU <b>DS</b> Competition</span>
    </a>
    <button class="nav-toggle" id="nav-toggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav class="nav-links" id="nav-links">
      <?php foreach (NAV as $href => $label): ?>
        <a href="<?= $href ?>" class="<?= $active === $href ? 'active' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
      <a href="agent.php" class="btn btn-primary btn-sm <?= $active === 'agent.php' ? 'active' : '' ?>">
        Access AI Agent <span aria-hidden="true">→</span>
      </a>
    </nav>
  </div>
</header>
<?php
}

function page_footer($showFull = true) {
    if ($showFull): ?>
<footer class="footer">
  <div class="container footer-grid">
    <div>
      <a href="index.php" class="brand">
        <span class="brand-mark">AI</span>
        <span class="brand-text">AU <b>DS</b> Competition</span>
      </a>
      <p class="muted">Data cleaning and analysis by conversation. Built for the Data Science Competition at Air University Karachi.</p>
    </div>
    <div>
      <h4>Pages</h4>
      <?php foreach (NAV as $href => $label): ?><a href="<?= $href ?>"><?= $label ?></a><?php endforeach; ?>
      <a href="agent.php">Access AI Agent</a>
    </div>
    <div>
      <h4>Built with</h4>
      <span>Claude AI</span><span>E2B Sandboxes</span><span>FastAPI · Python</span><span>PHP</span>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© <?= date('Y') ?> Air University Karachi · Computer Science</span>
    <span>Created by <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">Novatix Solution</a> · <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">novatixsolution.com</a></span>
  </div>
</footer>
<?php else: ?>
<div class="mini-footer">Created by <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">Novatix Solution</a> · <a href="https://novatixsolution.com" target="_blank" rel="noopener" class="novatix">novatixsolution.com</a></div>
<?php endif; ?>
<script src="assets/site.js"></script>
</body>
</html>
<?php
}
