<?php
// Zweck: reCAPTCHA v3 Prüffunktion + sicherer Bootstrap/Autoload-Fallback

function recaptcha_verify(string $token, string $expectedAction, float $minScore = 0.5): bool {
    // Secret holen
    $secret = getenv('RECAPTCHA_SECRET') ?: '';
    if ($secret === '' || $token === '') return false;

    // Request-Body bauen
    $post = http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    // HTTP-Context für POST
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post),
            'content' => $post,
            'timeout' => 5,
        ]
    ]);

    // Google-API aufrufen
    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) return false;

    // Antwort prüfen
    $res = json_decode($raw, true);
    if (!$res || empty($res['success'])) return false;
    if (!empty($res['action']) && $res['action'] !== $expectedAction) return false;

    // Score-Schwelle
    return (float)($res['score'] ?? 0.0) >= $minScore;
}

// CSRF-Helper laden
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';

// Bootstrap robust nachladen
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..'); // Projekt-Root
$__boot = $__root . '/app/bootstrap.php';
if (!defined('TOP_BOOTSTRAP_LOADED')) {
    if (is_file($__boot)) {
        require_once $__boot;
    } else {
        // harter Abbruch mit Pfad-Hinweis
        die('Autoload-Fehler: ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ' fehlt');
    }
}

// Composer-Autoload (falls vorhanden)
$vendor = ROOT_PATH . '/vendor/autoload.php';
if (is_file($vendor)) {
    require_once $vendor;
}
