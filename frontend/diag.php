<?php
// Health check for the live website: open diag.php?key=YOUR_DIAG_KEY
// Shows only pass/fail results, never secrets. Disabled unless DIAG_KEY is set in config.local.php.
require __DIR__ . '/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$diagKey = getenv('DIAG_KEY') ?: '';
if ($diagKey === '' || !hash_equals($diagKey, (string)($_GET['key'] ?? ''))) {
    http_response_code(404);
    exit('Not found');
}

@set_time_limit(BACKEND_TIMEOUT + 20);
$checks = [];
$add = function ($name, $ok, $detail) use (&$checks) { $checks[] = [$name, $ok, $detail]; };

$add('PHP version', version_compare(PHP_VERSION, '7.4', '>='), PHP_VERSION);
$add('cURL extension', function_exists('curl_init'), function_exists('curl_init') ? 'installed' : 'missing: enable it in hPanel > PHP Configuration');
$limit = (int) ini_get('max_execution_time');
$add('PHP time limit can be raised', $limit === 0 || $limit >= BACKEND_TIMEOUT, "now $limit s (needs 0 or at least " . BACKEND_TIMEOUT . ' s; Render can take ~60 s to wake)');
$add('config.local.php present', is_readable(__DIR__ . '/config.local.php'), is_readable(__DIR__ . '/config.local.php') ? 'found' : 'missing');
$add('BACKEND_URL set', BACKEND_URL !== 'http://127.0.0.1:8000', BACKEND_URL);
$add('BACKEND_SECRET set', BACKEND_SECRET !== '', BACKEND_SECRET !== '' ? strlen(BACKEND_SECRET) . ' characters' : 'empty');
$apiSrc = @file_get_contents(__DIR__ . '/api.php') ?: '';
$add('Latest api.php uploaded', strpos($apiSrc, "case 'ping'") !== false, strpos($apiSrc, "case 'ping'") !== false ? 'yes' : 'old version: upload the new files');

function probe($path, $withSecret) {
    $ch = curl_init(BACKEND_URL . $path);
    $headers = $withSecret ? ['X-Backend-Secret: ' . BACKEND_SECRET] : [];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => BACKEND_TIMEOUT, CURLOPT_HTTPHEADER => $headers]);
    $t = microtime(true);
    $body = curl_exec($ch);
    $res = [curl_getinfo($ch, CURLINFO_HTTP_CODE), round(microtime(true) - $t, 1), $body === false ? curl_error($ch) : ''];
    curl_close($ch);
    return $res;
}

if (function_exists('curl_init')) {
    [$code, $secs, $err] = probe('/health', false);
    $add('Hostinger can reach the backend', $code === 200, $code === 200 ? "HTTP 200 in {$secs}s" : ($err ?: "HTTP $code") . " after {$secs}s");
    [$code, $secs, $err] = probe('/models', true);
    $add('Secret matches the backend', $code === 200, $code === 200 ? 'yes' : ($code === 401 ? 'NO: BACKEND_SECRET here differs from Render' : ($err ?: "HTTP $code")));
}
$allOk = !in_array(false, array_column($checks, 1), true);
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Diagnostics</title>
<style>
  body { font: 15px/1.5 system-ui, sans-serif; background: #f5f7fb; color: #0f172a; margin: 0; padding: 20px 16px; }
  .box { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; }
  h1 { font-size: 20px; margin: 0 0 4px; }
  .sum { font-weight: 700; margin: 0 0 14px; color: <?= $allOk ? '#16a34a' : '#dc2626' ?>; }
  .row { display: flex; gap: 10px; padding: 9px 0; border-top: 1px solid #e2e8f0; }
  .ok, .bad { flex: none; width: 46px; font-weight: 800; font-size: 13px; }
  .ok { color: #16a34a; } .bad { color: #dc2626; }
  .name { font-weight: 600; } .detail { color: #5b6b82; font-size: 14px; overflow-wrap: anywhere; }
</style></head>
<body><div class="box">
  <h1>Website diagnostics</h1>
  <p class="sum"><?= $allOk ? 'Everything is working.' : 'Some checks failed. See the red lines.' ?></p>
  <?php foreach ($checks as [$name, $ok, $detail]): ?>
    <div class="row"><span class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? 'PASS' : 'FAIL' ?></span>
      <div><div class="name"><?= htmlspecialchars($name) ?></div><div class="detail"><?= htmlspecialchars((string)$detail) ?></div></div></div>
  <?php endforeach; ?>
</div></body></html>
