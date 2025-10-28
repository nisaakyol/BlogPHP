<?php
function recaptcha_verify(string $token, string $expectedAction, float $minScore = 0.5): bool {
    $secret = getenv('RECAPTCHA_SECRET') ?: '';
    if ($secret === '' || $token === '') return false;

    $post = http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post),
            'content' => $post,
            'timeout' => 5,
        ]
    ]);

    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) return false;

    $res = json_decode($raw, true);
    if (!$res || empty($res['success'])) return false;
    if (!empty($res['action']) && $res['action'] !== $expectedAction) return false;

    return (float)($res['score'] ?? 0.0) >= $minScore;
}

require_once ROOT_PATH . '/app/helpers/csrf.php';

$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..'); // -> Projekt-Root
$__boot = $__root . '/app/OOP/bootstrap.php';
if (!defined('TOP_BOOTSTRAP_LOADED')) {
    if (is_file($__boot)) {
        require_once $__boot;
    } else {
        // Klarer Fehler mit vollem Pfad
        die('Autoload-Fehler: ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ' fehlt');
    }
}
$vendor = ROOT_PATH . '/vendor/autoload.php';
if (is_file($vendor)) {
    require_once $vendor;
}
