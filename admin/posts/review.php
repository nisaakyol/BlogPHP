<?php
declare(strict_types=1);

require __DIR__ . '/../../path.php';                 // anpassen falls dein Pfad anders ist
require_once ROOT_PATH . '/app/includes/bootstrap.php';

use App\OOP\Repositories\DbRepository;

$action = $_GET['action'] ?? '';
$postId = (int)($_GET['id'] ?? 0);
$token  = $_GET['t'] ?? '';

// Token prüfen
$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'CHANGE_ME';
$valid  = hash_equals(hash_hmac('sha256', (string)$postId, $secret), $token);

if (!$valid || $postId <= 0 || !in_array($action, ['approve','reject'], true)) {
    http_response_code(403);
    echo 'Ungültiger oder abgelaufener Link.';
    exit;
}

$repo = new DbRepository();

// Reviewer-ID, falls jemand eingeloggt ist
$reviewerId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

if ($action === 'approve') {
    $repo->approvePost($postId, $reviewerId ?? 0, 'Freigegeben via Mail-Link');
    $msg = 'Post freigegeben.';
} else {
    $repo->rejectPost($postId, $reviewerId ?? 0, 'Abgelehnt via Mail-Link');
    $msg = 'Post abgelehnt.';
}

?>
<!doctype html>
<html lang="de">
<head><meta charset="utf-8"><title>Moderation</title>
<style>body{font-family:system-ui,Arial;padding:24px}a.button{display:inline-block;background:#0d6efd;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none}</style>
</head>
<body>
  <h2><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></h2>
</body>
</html>
