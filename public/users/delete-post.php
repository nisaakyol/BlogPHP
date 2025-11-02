<?php
declare(strict_types=1);

require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';

use App\Infrastructure\Repositories\DbRepository;

usersOnly(); // Zugriff nur für eingeloggte
if (session_status() !== PHP_SESSION_ACTIVE) session_start(); // Session sicherstellen

$userId = (int)($_SESSION['id'] ?? 0);
$postId = (int)($_GET['id'] ?? 0);

if ($postId <= 0) { // ID prüfen
    $_SESSION['message'] = 'Ungültige Post-ID.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts');
    exit;
}

$repo = new DbRepository();
$post = $repo->selectOne('posts', ['id' => $postId]);

if (!$post) { // Existenz prüfen
    $_SESSION['message'] = 'Post nicht gefunden.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts');
    exit;
}

// Berechtigung: Besitzer oder Admin
$isOwner = (int)($post['user_id'] ?? 0) === $userId;
$isAdmin = !empty($_SESSION['admin']);

if (!$isOwner && !$isAdmin) {
    $_SESSION['message'] = 'Nicht erlaubt.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts');
    exit;
}

try {
    // Löschen: bevorzugt delete(), sonst Fallback-Query
    if (method_exists($repo, 'delete')) {
        $repo->delete('posts', $postId);
    } else {
        $pdo = $repo->getPdo();
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $postId]);
    }

    $_SESSION['message'] = 'Post erfolgreich gelöscht.';
    $_SESSION['type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['message'] = 'Löschen fehlgeschlagen: ' . $e->getMessage();
    $_SESSION['type'] = 'error';
}

header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts');
exit;
