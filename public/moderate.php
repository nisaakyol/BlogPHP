<?php
declare(strict_types=1);

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';

use App\Infrastructure\Repositories\DbRepository;

// Repo & Secret initialisieren
$repo   = new DbRepository();
// Secret für HMAC-Signaturen (Fallback 'dev' – in Prod ersetzen!)
$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';

// URL-Parameter einlesen
$id     = (int)($_GET['id'] ?? 0);
$action = (string)($_GET['action'] ?? '');
$exp    = (int)($_GET['exp'] ?? 0);
$sig    = (string)($_GET['sig'] ?? '');

// Erwarteten Payload bilden und HMAC lokal berechnen
$payload = $id . '|' . $action . '|' . $exp;
$calcSig = hash_hmac('sha256', $payload, $secret);

// /Link-Gültigkeit prüfen: id>0, erlaubte Action, korrekte Signatur, nicht abgelaufen
$valid = $id > 0
    && in_array($action, ['approve', 'reject'], true)
    && hash_equals($sig, $calcSig)
    && time() < $exp;

header('Content-Type: text/html; charset=utf-8');

// Ungültige oder abgelaufene Links: 403 + Hinweis
if (!$valid) {
    http_response_code(403);
    echo '<h2>Ungültiger oder abgelaufener Link.</h2>';
    echo '<p><a href="' . BASE_URL . '">Zur Startseite</a></p>';
    exit;
}

// Reviewer-ID bestimmen
// Wenn Admin eingeloggt → dessen ID, sonst 0 (E-Mail-Moderation ohne Login)
$reviewerId = (isset($_SESSION['admin'], $_SESSION['id']) && $_SESSION['admin'])
    ? (int)$_SESSION['id']
    : 0;

try {
    if ($action === 'approve') {
        // Beitrag freigeben
        $repo->approvePost($id, $reviewerId, null);
        $msg = 'Beitrag freigegeben.';
    } else {
        // Beitrag ablehnen (mit Standardgrund)
        $repo->rejectPost($id, $reviewerId, 'Abgelehnt via E-Mail-Link');
        $msg = 'Beitrag abgelehnt.';
    }
} catch (Throwable $e) {
    // Fehlerfall: 500 + Meldung ausgeben
    http_response_code(500);
    echo '<h2>Fehler: Aktion konnte nicht ausgeführt werden.</h2>';
    echo '<pre style="white-space:pre-wrap">' . htmlspecialchars($e->getMessage()) . '</pre>';
    exit;
}

// Einfaches Feedback an den Nutzer; alternativ könnte ein Redirect erfolgen
echo '<h2>' . $msg . '</h2>';
