<?php
declare(strict_types=1);
// --- Bootstrap ---------------------------------------------------------------
if (!defined('ROOT_PATH')) {
    $path = dirname(__DIR__, 2) . '/path.php';
    if (is_file($path)) {
        require_once $path; // definiert ROOT_PATH/BASE_URL
    } else {
        define('ROOT_PATH', dirname(__DIR__, 2));
        if (!defined('BASE_URL')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            define('BASE_URL', $scheme . '://' . $host);
        }
    }
}
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/send-email.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// --- Nur POST ----------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// --- Helpers -----------------------------------------------------------------
function first_post(array $names): string {
    foreach ($names as $n) {
        if (!isset($_POST[$n])) continue;
        $val = (string)$_POST[$n];
        $val = trim($val);
        if ($val !== '') return $val;
    }
    return '';
}

/** finde 1. POST-Wert, der wie eine Mail aussieht (Fallback) */
function first_email_like_from_post(): string {
    foreach ($_POST as $v) {
        $v = trim((string)$v);
        if ($v === '') continue;
        if (preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $v)) return $v;
    }
    return '';
}

/** unsichtbare Whitespaces entfernen (Zero-Width/NBSP) */
function strip_invisible_ws(string $s): string {
    // nur gezielte Zeichen entfernen, KEINE kompletten \p{Z}-Klassen
    return preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $s) ?? $s;
}

/** sehr einfache, stabile E-Mail-Validierung */
function is_simple_email(string $email): bool {
    $email = strip_invisible_ws(trim($email));
    return (bool)preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email);
}

// --- Eingaben ----------------------------------------------------------------
$email   = first_post(['contact_email','email','from','from_email','your-email','mail']);
if ($email === '') {
    $email = first_email_like_from_post(); // Fallback
}
$message = first_post(['contact_message','message','msg','nachricht']);

// Honeypot (unsichtbar): falls befüllt -> still ok, aber nicht senden
if (!empty($_POST['website'] ?? '')) {
    $_SESSION['message'] = 'Danke! Deine Nachricht wurde gesendet.';
    $_SESSION['type']    = 'success';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL));
    exit;
}

// --- Validierung --------------------------------------------------------------
$errors = [];
if ($email === '' || !is_simple_email($email)) { $errors[] = 'Bitte eine gültige E-Mail angeben.'; }
if ($message === '' || mb_strlen($message) < 2) { $errors[] = 'Bitte eine Nachricht eingeben.'; }

if ($errors) {
    $_SESSION['message'] = implode(' ', $errors);
    $_SESSION['type']    = 'error';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL));
    exit;
}

// --- Mail (nur zwei Felder) --------------------------------------------------
$payload = [
    'from_email' => strip_invisible_ws(trim($email)),
    'message'    => $message,
];

$ok = send_admin_mail($payload, null, 'Kontakt');

$_SESSION['message'] = $ok ? 'Danke! Deine Nachricht wurde gesendet.' : 'Senden fehlgeschlagen.';
$_SESSION['type']    = $ok ? 'success' : 'error';

$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL;
header('Location: ' . $back);
exit;
