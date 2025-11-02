<?php
declare(strict_types=1);

// Zweck: Zentrale Zugriffs-/Rechteprüfungen (Login-Pflicht, Admin-Pflicht, Post/Comment-Ownership)

namespace App\Infrastructure\Services;

use App\Infrastructure\Repositories\PostRepository;
use App\Infrastructure\Repositories\CommentRepository;

class AccessService
{
    public function __construct(
        private AuthService $auth,
        private PostRepository $posts,
        private CommentRepository $comments
    ) {}

    // Erzwingt eingeloggten Nutzer, sonst Redirect auf /public/login.php
    public static function requireUser(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/public/login.php');
            exit();
        }
    }

    // Erzwingt Admin-Rechte, sonst Redirect (unauth: /public/login.php, kein Admin: /public/index.php)
    public static function requireAdmin(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/public/login.php');
            exit();
        }

        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Nicht erlaubt';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/public/index.php');
            exit();
        }
    }

    // true, wenn aktueller User Admin ist
    public function isAdmin(): bool
    {
        if (method_exists($this->auth, 'currentUserIsAdmin')) {
            return (bool) $this->auth->currentUserIsAdmin();
        }

        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['admin']);
    }

    // Liefert die ID des eingeloggten Users oder null (Fallback über $_SESSION)
    public function currentUserId(): ?int
    {
        if (method_exists($this->auth, 'currentUserId')) {
            $id = $this->auth->currentUserId();
            return $id !== null ? (int) $id : null;
        }

        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
    }

    // Darf der aktuelle Benutzer den Post verwalten? (Admin: immer; sonst nur eigene)
    public function canManagePost(int $postId): bool
    {
        if ($this->isAdmin()) return true;

        $uid = $this->currentUserId();
        if ($uid === null) return false;

        $post = $this->posts->findById($postId);
        if (!$post) return false;

        return (int)($post['user_id'] ?? 0) === $uid;
    }

    // Darf der aktuelle Benutzer den Kommentar verwalten? (eigene + Kommentare unter eigenen Posts)
    public function canManageComment(int $commentId): bool
    {
        if ($this->isAdmin()) return true;

        $uid = $this->currentUserId();
        if ($uid === null) return false;

        $comment = $this->comments->findById($commentId);
        if (!$comment) return false;

        if ((int)($comment['user_id'] ?? 0) === $uid) return true;

        $postId = (int)($comment['post_id'] ?? 0);
        if ($postId > 0) {
            $post = $this->posts->findById($postId);
            if ($post && (int)($post['user_id'] ?? 0) === $uid) {
                return true;
            }
        }

        return false;
    }
}
