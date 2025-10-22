<?php
declare(strict_types=1);

require_once __DIR__ . '/../_admin_boot.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';
require_once ROOT_PATH . '/app/helpers/csrf.php';

use App\OOP\Repositories\DbRepository;
use App\OOP\Services\MailerService;

usersOnly();
if (empty($_SESSION['admin'])) {
  $_SESSION['message'] = 'Nicht erlaubt';
  $_SESSION['type'] = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/admin/posts/index.php'); exit;
}

if (!csrf_check($_POST['csrf'] ?? '')) {
  $_SESSION['message'] = 'Ungültiges CSRF-Token';
  $_SESSION['type'] = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php'); exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$action = (string)($_POST['action'] ?? '');
$note   = trim((string)($_POST['note'] ?? ''));

$repo = new DbRepository();
$ok   = 0;

if ($action === 'approve') {
  $ok = $repo->approvePost($postId, (int)$_SESSION['id'], $note ?: null);
} elseif ($action === 'reject') {
  $ok = $repo->rejectPost($postId, (int)$_SESSION['id'], $note ?: null);
} else {
  $_SESSION['message'] = 'Unbekannte Aktion';
  $_SESSION['type'] = 'error';
  header('Location: ' . BASE_URL . '/admin/posts/index.php'); exit;
}

if ($ok) {
  // Optional: Autor benachrichtigen (einfacher Mailer)
  try {
    MailerService::send([
      'Event' => $action === 'approve' ? 'Post freigegeben' : 'Post abgelehnt',
      'PostID' => $postId,
      'Reviewer' => $_SESSION['username'] ?? 'Admin',
      'Hinweis'  => $note,
      'URL'      => BASE_URL . '/single.php?id=' . $postId,
    ]);
  } catch (\Throwable $e) { /* still */ }

  $_SESSION['message'] = $action === 'approve' ? 'Post freigegeben' : 'Post abgelehnt';
  $_SESSION['type'] = 'success';
} else {
  $_SESSION['message'] = 'Keine Änderung durchgeführt';
  $_SESSION['type'] = 'error';
}

header('Location: ' . BASE_URL . '/admin/posts/index.php'); exit;
