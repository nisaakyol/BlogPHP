<?php
// Datei: app/Support/helpers/Contact.php
// Zweck: Kontaktformular verarbeiten, Eingaben validieren und Admin-E-Mail senden (mit Honeypot, Flash-Messages, Redirect)

declare(strict_types=1);

$ROOT = dirname(__DIR__, 3);            // app/Support/helpers -> drei Ebenen hoch = Projektwurzel
require_once $ROOT . '/public/path.php';

require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/send-email.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Erstes nicht-leeres Feld aus $_POST anhand einer Namensliste lesen
function first_post(array $names): string {
    foreach ($names as $n) {
        if (!isset($_POST[$n])) continue;
        $val = (string)$_POST[$n];
        $val = trim($val);
        if ($val !== '') return $val;
    }
    return '';
}

// Erstbeste E-Mail-ähnliche Zeichenkette aus $_POST erkennen
function first_email_like_from_post(): string {
    foreach ($_POST as $v) {
        $v = trim((string)$v);
        if ($v === '') continue;
        if (preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $v)) return $v;
    }
    return '';
}

// Unsichtbare Whitespaces (NBSP/Zero-Width) entfernen
function strip_invisible_ws(string $s): string {
    return preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $s) ?? $s;
}

// Einfache, robuste E-Mail-Validierung
function is_simple_email(string $email): bool {
    $email = strip_invisible_ws(trim($email));
    return (bool)preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email);
}

// Eingaben sammeln
$email   = first_post(['contact_email','email','from','from_email','your-email','mail']);
if ($email === '') $email = first_email_like_from_post();
$message = first_post(['contact_message','message','msg','nachricht']);

// Honeypot: wenn befüllt, stiller Erfolg
if (!empty($_POST['website'] ?? '')) {
    $_SESSION['message'] = 'Danke! Deine Nachricht wurde gesendet.';
    $_SESSION['type']    = 'success';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL));
    exit;
}

// Validierung
$errors = [];
if ($email === '' || !is_simple_email($email)) $errors[] = 'Bitte eine gültige E-Mail angeben.';
if ($message === '' || mb_strlen($message) < 2) $errors[] = 'Bitte eine Nachricht eingeben.';

if ($errors) {
    $_SESSION['message'] = implode(' ', $errors);
    $_SESSION['type']    = 'error';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL));
    exit;
}

// Mail-Payload
$payload = [
    'from_email' => strip_invisible_ws(trim($email)),
    'message'    => $message,
];

// Versand
$ok = send_admin_mail($payload, null, 'Kontakt');

// Feedback + Redirect zurück
$_SESSION['message'] = $ok ? 'Danke! Deine Nachricht wurde gesendet.' : 'Senden fehlgeschlagen.';
$_SESSION['type']    = $ok ? 'success' : 'error';

$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL;
header('Location: ' . $back);
exit;
