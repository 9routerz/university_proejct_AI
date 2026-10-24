<?php
// Shared setup for every PHP page: settings, secure session, security headers.

// ── Settings ─────────────────────────────────────────────────────────────────
// Read from a .env file. The safest place is one folder ABOVE public_html
// (ds-competition.env), where the web server can never serve it.
// php-frontend/.env also works; .htaccess blocks it from the web.
function load_env_file($path) {
    if (!is_readable($path)) return false;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (getenv($key) === false) putenv("$key=" . trim($value, "\"'"));
    }
    return true;
}
load_env_file(dirname(__DIR__) . '/ds-competition.env') || load_env_file(__DIR__ . '/.env');

define('BACKEND_URL', rtrim(getenv('BACKEND_URL') ?: 'http://127.0.0.1:8000', '/'));
define('BACKEND_SECRET', getenv('BACKEND_SECRET') ?: '');
define('ACCESS_CODE', getenv('ACCESS_CODE') ?: '');   // empty = anyone with the link can join
// Render's free plan can take ~50s to wake up, and one agent reply can take a while.
define('BACKEND_TIMEOUT', 180);

// ── Secure session cookie ────────────────────────────────────────────────────
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('dscomp');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,      // JavaScript can't read the cookie
    'samesite' => 'Strict',  // other sites can't send requests with it
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ── Security headers ─────────────────────────────────────────────────────────
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000');
}
