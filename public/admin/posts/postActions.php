<?php
declare(strict_types=1);

// Admin-Bootstrap + Guards/Session
require_once __DIR__ . '/../_admin_boot.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';

use App\Infrastructure\Repositories\DbRepository;

usersOnly(); // nur eingeloggte User

// nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// CSRF prüfen
csrf_validate_or_die($_POST['csrf_token'] ?? '');

// Eingaben
$action  = (string)($_POST['action'] ?? '');
$postId  = (int)($_POST['post_id'] ?? 0);
$userId  = (int)($_SESSION['id'] ?? 0);
$isAdmin = !empty($_SESSION['admin'] ?? 0);

$repo = new DbRepository();

// Post laden
$post = $repo->selectOne('posts', ['id' => $postId]);
if (!$post) {
  $_SESSION['message'] = 'Beitrag nicht gefunden.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
  exit;
}

// Besitzer prüfen
$isOwner = ((int)($post['user_id'] ?? 0) === $userId);

switch ($action) {
  case 'submit':
    // nur Besitzer; nicht Admin
    if (!$isOwner || $isAdmin) {
      $_SESSION['message'] = 'Nicht erlaubt.';
      $_SESSION['type']    = 'error';
      break;
    }
    // nur aus draft/rejected
    $status = (string)($post['status'] ?? 'draft');
    if (!in_array($status, ['draft', 'rejected'], true)) {
      $_SESSION['message'] = 'Dieser Beitrag kann nicht eingereicht werden.';
      $_SESSION['type']    = 'error';
      break;
    }
    $rows = $repo->submitPost($postId, $userId);
    if ($rows > 0) {
      $_SESSION['message'] = 'Beitrag zur Prüfung eingereicht.';
      $_SESSION['type']    = 'success';
    } else {
      $_SESSION['message'] = 'Es wurde nichts geändert.';
      $_SESSION['type']    = 'error';
    }
    break;

  case 'delete':
    // Besitzer oder Admin
    if (!$isOwner && !$isAdmin) {
      $_SESSION['message'] = 'Nicht erlaubt.';
      $_SESSION['type']    = 'error';
      break;
    }
    try {
      // Reihenfolge: Kommentare → Pivot → Post
      $repo->deleteCommentsByPost($postId);
      $repo->deletePostTopics($postId);
      $rows = $repo->deletePost($postId);
      if ($rows > 0) {
        $_SESSION['message'] = 'Beitrag gelöscht.';
        $_SESSION['type']    = 'success';
      } else {
        $_SESSION['message'] = 'Beitrag konnte nicht gelöscht werden.';
        $_SESSION['type']    = 'error';
      }
    } catch (\Throwable $e) {
      $_SESSION['message'] = 'Fehler beim Löschen: ' . $e->getMessage();
      $_SESSION['type']    = 'error';
    }
    break;

  default:
    $_SESSION['message'] = 'Unbekannte Aktion.';
    $_SESSION['type']    = 'error';
    break;
}

header('Location: ' . BASE_URL . '/public/admin/posts/index.php');
exit;
?>