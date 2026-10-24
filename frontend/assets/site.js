// Mobile menu toggle
const toggle = document.getElementById('nav-toggle');
const links = document.getElementById('nav-links');
if (toggle && links) {
  const setMenu = (open) => {
    links.classList.toggle('open', open);
    toggle.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    document.body.classList.toggle('menu-open', open);
  };
  toggle.addEventListener('click', () => setMenu(!links.classList.contains('open')));
  // Close when a link is tapped, when tapping outside, on Escape, or when the screen gets wide
  links.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
  document.addEventListener('click', (e) => {
    if (links.classList.contains('open') && !links.contains(e.target) && !toggle.contains(e.target)) setMenu(false);
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });
  window.matchMedia('(min-width: 881px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });
}

// Guide page: click a prompt chip to copy it
const guideChips = document.getElementById('guide-chips');
if (guideChips) {
  guideChips.addEventListener('click', async (e) => {
    const chip = e.target.closest('.chip');
    if (!chip) return;
    const text = chip.textContent;
    try { await navigator.clipboard.writeText(text); } catch (err) { /* clipboard blocked: just show feedback */ }
    chip.textContent = '✓ Copied';
    setTimeout(() => { chip.textContent = text; }, 1200);
  });
}

// Fade sections in as they scroll into view
const revealables = document.querySelectorAll('.reveal');
if ('IntersectionObserver' in window && revealables.length) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  revealables.forEach((el) => io.observe(el));
} else {
  revealables.forEach((el) => el.classList.add('visible'));
}

// Hero terminal: type out a sample conversation, then loop
const term = document.getElementById('hero-terminal');
if (term) {
  const script = [
    ['prompt', 'how many missing values are in each column?'],
    ['code', "print(df.isnull().sum())"],
    ['out', 'Age            120\nTown_Area       80\nHousehold_Income 16'],
    ['prompt', 'fill missing Age with the median'],
    ['code', "df['Age'] = df['Age'].fillna(df['Age'].median())"],
    ['out', '✓ Age: 0 missing values left'],
  ];
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

  async function play() {
    term.innerHTML = '';
    for (const [kind, text] of script) {
      const line = document.createElement('div');
      line.className = 'term-' + kind;
      term.appendChild(line);
      if (kind === 'prompt' && !reduce) {
        for (const ch of text) { line.textContent += ch; await sleep(28); }
        await sleep(350);
      } else {
        line.textContent = text;
        await sleep(reduce ? 0 : 650);
      }
    }
    if (!reduce) { await sleep(3500); play(); }
  }
  play();
}
