<?php
// Small proxy between the browser and the FastAPI backend.
// The browser only talks to this file. The backend URL, BACKEND_SECRET and the
// participant's session_id stay on the server (in .env and the PHP session).

require __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

function reply($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function backend($method, $path, $body = null) {
    $ch = curl_init(BACKEND_URL . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => BACKEND_TIMEOUT,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-Backend-Secret: ' . BACKEND_SECRET],
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return [502, ['detail' => 'The server is waking up or unreachable. Please try again in a minute.']];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = ['detail' => 'Unexpected reply from the server.'];
    }
    if ($status === 401) {
        // Wrong BACKEND_SECRET: an organizer setup problem, not the participant's fault
        $data = ['detail' => 'Server configuration error. Please tell the organizer.'];
        $status = 500;
    }
    return [$status, $data];
}

// Allow at most one call per $seconds for this browser session
function rate_limit($key, $seconds) {
    $now = microtime(true);
    $last = $_SESSION['rl_' . $key] ?? 0;
    if ($now - $last < $seconds) {
        reply(429, ['detail' => 'Too fast. Please wait a moment.']);
    }
    $_SESSION['rl_' . $key] = $now;
}

// ── Basic checks on every request ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, ['detail' => 'Method not allowed.']);
}
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf'], $csrf)) {
    reply(403, ['detail' => 'Your page has expired. Please refresh.']);
}
if (BACKEND_SECRET === '') {
    reply(500, ['detail' => 'Server configuration error. Please tell the organizer.']);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $_GET['action'] ?? '';
$sessionId = $_SESSION['session_id'] ?? null;

// The session lock would make parallel requests from this browser wait;
// release it once we've read what we need (we reopen it to write).
function save_session(array $values) {
    session_start();
    foreach ($values as $k => $v) {
        if ($v === null) unset($_SESSION[$k]); else $_SESSION[$k] = $v;
    }
    session_write_close();
}

switch ($action) {
    case 'start':
        if ($sessionId) reply(409, ['detail' => 'You already have a running session. Refresh the page.']);

        if (ACCESS_CODE !== '') {
            $fails = $_SESSION['code_fails'] ?? 0;
            if ($fails >= 5 && time() - ($_SESSION['code_fail_time'] ?? 0) < 300) {
                reply(429, ['detail' => 'Too many wrong codes. Try again in 5 minutes.']);
            }
            if (!hash_equals(ACCESS_CODE, (string)($input['code'] ?? ''))) {
                $_SESSION['code_fails'] = $fails + 1;
                $_SESSION['code_fail_time'] = time();
                reply(403, ['detail' => 'Wrong access code.']);
            }
            unset($_SESSION['code_fails'], $_SESSION['code_fail_time']);
        }

        $name = trim(strip_tags((string)($input['name'] ?? '')));
        if ($name === '' || mb_strlen($name) > 60) {
            reply(400, ['detail' => 'Please enter your name (max 60 characters).']);
        }
        rate_limit('start', 10);
        session_write_close();

        [$status, $data] = backend('POST', '/start', ['participant_name' => $name]);
        if ($status === 200) {
            session_start();
            session_regenerate_id(true);
            session_write_close();
            save_session(['session_id' => $data['session_id'], 'name' => $name]);
            unset($data['session_id']);
        }
        reply($status, $data);

    case 'chat':
        if (!$sessionId) reply(401, ['detail' => 'No active session. Refresh the page.']);
        $message = trim((string)($input['message'] ?? ''));
        if ($message === '') reply(400, ['detail' => 'Message is empty.']);
        if (mb_strlen($message) > 2000) reply(400, ['detail' => 'Message is too long (max 2000 characters).']);
        rate_limit('chat', 2);
        session_write_close();

        [$status, $data] = backend('POST', '/chat', ['session_id' => $sessionId, 'message' => $message]);
        if ($status === 404 || $status === 403) save_session(['session_id' => null]);
        reply($status, $data);

    case 'status':
        if (!$sessionId) reply(401, ['detail' => 'No active session.']);
        session_write_close();
        [$status, $data] = backend('GET', '/status/' . rawurlencode($sessionId));
        if ($status === 404 || $status === 403) save_session(['session_id' => null]);
        unset($data['session_id']);
        reply($status, $data);

    case 'score':
        if (!$sessionId) reply(401, ['detail' => 'No active session.']);
        rate_limit('score', 10);
        session_write_close();
        [$status, $data] = backend('POST', '/score/' . rawurlencode($sessionId));
        if ($status === 200) save_session(['session_id' => null]);
        reply($status, $data);

    default:
        reply(404, ['detail' => 'Unknown action.']);
}
