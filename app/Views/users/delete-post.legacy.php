<?php
declare(strict_types=1);

require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';

use App\OOP\Repositories\DbRepository;

usersOnly();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$userId = (int)($_SESSION['id'] ?? 0);
$postId = (int)($_GET['id'] ?? 0);

if ($postId <= 0) {
    $_SESSION['message'] = 'Ungültige Post-ID.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
    exit;
}

$repo = new DbRepository();
$post = $repo->selectOne('posts', ['id' => $postId]);

if (!$post) {
    $_SESSION['message'] = 'Post nicht gefunden.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
    exit;
}

// Nur Besitzer (oder Admin) dürfen löschen
$isOwner = (int)($post['user_id'] ?? 0) === $userId;
$isAdmin = !empty($_SESSION['admin']);

if (!$isOwner && !$isAdmin) {
    $_SESSION['message'] = 'Nicht erlaubt.';
    $_SESSION['type'] = 'error';
    header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
    exit;
}

try {
    // Bevorzugt generische delete()-Methode nutzen, falls vorhanden
    if (method_exists($repo, 'delete')) {
        $repo->delete('posts', $postId);
    } else {
        // Fallback: eigene Lösch-Query (Ownership absichern)
        $pdo = $repo->getPdo(); // falls deine DbRepository getPdo() anbietet
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $postId]);
    }

    $_SESSION['message'] = 'Post erfolgreich gelöscht.';
    $_SESSION['type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['message'] = 'Löschen fehlgeschlagen: ' . $e->getMessage();
    $_SESSION['type'] = 'error';
}

header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
exit;
