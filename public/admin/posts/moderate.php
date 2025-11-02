<?php
declare(strict_types=1);

require_once __DIR__ . '/../_admin_boot.php';                               // Session, ROOT_PATH, BASE_URL, Guards
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';             // Autoload/DB
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';             // usersOnly/adminOnly
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';                   // CSRF-Helpers
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Infrastructure/Services/MailerService.php';

use App\Infrastructure\Repositories\DbRepository;
use App\Infrastructure\Services\MailerService;

usersOnly();                                                                // nur eingeloggte Nutzer

// Admin-Pflicht
if (empty($_SESSION['admin'])) {
  $_SESSION['message'] = 'Nicht erlaubt.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// CSRF prüfen (unterstützt csrf_token oder csrf)
$csrfToken = $_POST['csrf_token'] ?? $_POST['csrf'] ?? null;
csrf_validate_or_die($csrfToken);

// Eingaben lesen
$postId     = (int)($_POST['post_id'] ?? 0);
$action     = strtolower((string)($_POST['action'] ?? ''));
$note       = trim((string)($_POST['note'] ?? ''));
$reviewerId = (int)($_SESSION['id'] ?? 0);

// Post-ID validieren
if ($postId <= 0) {
  $_SESSION['message'] = 'Ungültige Post-ID.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Aktion prüfen
if (!in_array($action, ['approve','reject'], true)) {
  $_SESSION['message'] = 'Unbekannte Aktion.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Note begrenzen
if ($note !== '' && mb_strlen($note) > 5000) {
  $note = mb_substr($note, 0, 5000);
}

$repo = new DbRepository();

// Post laden
$post = $repo->selectOne('posts', ['id' => $postId]);
if (!$post) {
  $_SESSION['message'] = 'Post wurde nicht gefunden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Eigene Beiträge nicht moderieren
if ((int)$post['user_id'] === $reviewerId) {
  $_SESSION['message'] = 'Eigene Beiträge dürfen nicht moderiert werden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Statusprüfung
$allowedFromStates = ['submitted', 'rejected', 'draft'];
$currentStatus     = (string)($post['status'] ?? 'draft');
if (!in_array($currentStatus, $allowedFromStates, true)) {
  $_SESSION['message'] = 'Dieser Beitrag kann im aktuellen Status nicht moderiert werden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Moderation ausführen
$ok = 0;
try {
  if ($action === 'approve') {
    $ok = $repo->approvePost($postId, $reviewerId, $note ?: null);
  } else {
    $ok = $repo->rejectPost($postId, $reviewerId, $note ?: null);
  }
} catch (\Throwable $e) {
  $ok = 0; // Fehlerfall
}

// Mail + Feedback
if ($ok) {
  try {
    MailerService::send([
      'Event'    => $action === 'approve' ? 'Post freigegeben' : 'Post abgelehnt',
      'PostID'   => $postId,
      'Reviewer' => $_SESSION['username'] ?? 'Admin',
      'Hinweis'  => $note,
      'URL'      => BASE_URL . '/public/single.php?id=' . $postId,
    ]);
  } catch (\Throwable $e) {
    // Mailfehler ignorieren (optional: loggen)
  }

  $_SESSION['message'] = $action === 'approve'
    ? 'Post wurde freigegeben.'
    : 'Post wurde abgelehnt.';
  $_SESSION['type'] = 'success';
} else {
  $_SESSION['message'] = 'Keine Änderung durchgeführt.';
  $_SESSION['type']    = 'error';
}

// Zurück zur Übersicht
header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
exit;
