<?php
declare(strict_types=1);

/**
 * Datei: admin/posts/moderate.php
 * Zweck: Admin-Moderation von Posts (approve / reject)
 */

require_once __DIR__ . '/../_admin_boot.php';
require_once ROOT_PATH . '/app/includes/bootstrap.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';
require_once ROOT_PATH . '/app/helpers/csrf.php';

use App\OOP\Repositories\DbRepository;
use App\OOP\Services\MailerService;

// ─────────────────────────────────────────────────────────────────────────────
// Guards
// ─────────────────────────────────────────────────────────────────────────────
usersOnly();

if (empty($_SESSION['admin'])) {
  $_SESSION['message'] = 'Nicht erlaubt.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

// CSRF prüfen (Form-Feld: csrf_token; Fallback: csrf)
$csrfToken = $_POST['csrf_token'] ?? $_POST['csrf'] ?? null;
csrf_validate_or_die($csrfToken);

// Inputs
$postId     = (int)($_POST['post_id'] ?? 0);
$action     = strtolower((string)($_POST['action'] ?? ''));
$note       = trim((string)($_POST['note'] ?? ''));
$reviewerId = (int)($_SESSION['id'] ?? 0);

if ($postId <= 0) {
  $_SESSION['message'] = 'Ungültige Post-ID.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

if (!in_array($action, ['approve','reject'], true)) {
  $_SESSION['message'] = 'Unbekannte Aktion.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

if ($note !== '' && mb_strlen($note) > 5000) {
  $note = mb_substr($note, 0, 5000);
}

// ─────────────────────────────────────────────────────────────────────────────
// Post laden + Validierungen (Existenz, Self-Moderation, Status)
// ─────────────────────────────────────────────────────────────────────────────
$repo = new DbRepository();
$post = $repo->selectOne('posts', ['id' => $postId]);   // erwartet es gibt selectOne()

if (!$post) {
  $_SESSION['message'] = 'Post wurde nicht gefunden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

// Self-Moderation blockieren
if ((int)$post['user_id'] === $reviewerId) {
  $_SESSION['message'] = 'Eigene Beiträge dürfen nicht moderiert werden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

// Optional: Nur moderierbar, wenn eingereicht
$allowedFromStates = ['submitted', 'rejected', 'draft']; // passe an dein Workflow-Modell an
$currentStatus     = (string)($post['status'] ?? 'draft');
if (!in_array($currentStatus, $allowedFromStates, true)) {
  $_SESSION['message'] = 'Dieser Beitrag kann im aktuellen Status nicht moderiert werden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php');
  exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Aktion ausführen (Repository schützt zusätzlich via WHERE user_id <> reviewer)
// ─────────────────────────────────────────────────────────────────────────────
$ok = 0;
try {
  if ($action === 'approve') {
    $ok = $repo->approvePost($postId, $reviewerId, $note ?: null);
  } else {
    $ok = $repo->rejectPost($postId, $reviewerId, $note ?: null);
  }
} catch (\Throwable $e) {
  // optional loggen
  // error_log((string)$e);
  $ok = 0;
}

// ─────────────────────────────────────────────────────────────────────────────
// Ergebnis + optionale Benachrichtigung
// ─────────────────────────────────────────────────────────────────────────────
if ($ok) {
  try {
    MailerService::send([
      'Event'    => $action === 'approve' ? 'Post freigegeben' : 'Post abgelehnt',
      'PostID'   => $postId,
      'Reviewer' => $_SESSION['username'] ?? 'Admin',
      'Hinweis'  => $note,
      'URL'      => BASE_URL . '/single.php?id=' . $postId,
    ]);
  } catch (\Throwable $e) {
    // still ok
  }

  $_SESSION['message'] = $action === 'approve'
    ? 'Post wurde freigegeben.'
    : 'Post wurde abgelehnt.';
  $_SESSION['type']    = 'success';
} else {
  $_SESSION['message'] = 'Keine Änderung durchgeführt.';
  $_SESSION['type']    = 'error';
}

header('Location: ' . BASE_URL . '/admin/posts/index.php');
exit;
