<?php
declare(strict_types=1);

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';

use App\OOP\Repositories\DbRepository;
// Wenn du Login für E-Mail-Klicks erzwingen willst, auskommentieren:
// require_once ROOT_PATH . '/app/helpers/middleware.php'; adminOnly();

$repo   = new DbRepository();
$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';

// --- Query-Parameter aus E-Mail-Button ---------------------------------------
$id     = (int)($_GET['id'] ?? 0);
$action = (string)($_GET['action'] ?? '');
$exp    = (int)($_GET['exp'] ?? 0);
$sig    = (string)($_GET['sig'] ?? '');

// --- Signatur & Ablauf prüfen -------------------------------------------------
$payload = $id . '|' . $action . '|' . $exp;
$calcSig = hash_hmac('sha256', $payload, $secret);

$valid = $id > 0
  && in_array($action, ['approve','reject'], true)
  && hash_equals($sig, $calcSig)
  && time() < $exp;

header('Content-Type: text/html; charset=utf-8');

if (!$valid) {
    http_response_code(403);
    echo '<h2>Ungültiger oder abgelaufener Link.</h2>';
    echo '<p><a href="'.BASE_URL.'">Zur Startseite</a></p>';
    exit;
}

// Reviewer-ID: wenn Admin eingeloggt, dessen ID – sonst 0 (E-Mail-Moderation ohne Login)
$reviewerId = (isset($_SESSION['admin'], $_SESSION['id']) && $_SESSION['admin'])
  ? (int)$_SESSION['id']
  : 0;

// --- Aktion ausführen ---------------------------------------------------------
try {
    if ($action === 'approve') {
        $repo->approvePost($id, $reviewerId, null);
        $msg = 'Beitrag freigegeben.';
    } else {
        $repo->rejectPost($id, $reviewerId, 'Abgelehnt via E-Mail-Link');
        $msg = 'Beitrag abgelehnt.';
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Fehler: Aktion konnte nicht ausgeführt werden.</h2>';
    echo '<pre style="white-space:pre-wrap">'.htmlspecialchars($e->getMessage()).'</pre>';
    exit;
}

// --- Ausgabe / Redirect -------------------------------------------------------
echo '<h2>'.$msg.'</h2>';

