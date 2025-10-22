<?php
declare(strict_types=1);

namespace App\OOP\Services;

use App\OOP\Repositories\PostRepository;
use App\OOP\Repositories\CommentRepository;

/**
 * AccessService
 *
 * - Statische Guards: requireUser(), requireAdmin()
 * - Instanz-Checks: isAdmin(), currentUserId(), canManagePost(), canManageComment()
 *
 * Idee:
 *   * Alle Eingangsseiten (Dashboard, /admin/...) rufen requireUser() bzw. requireAdmin() auf.
 *   * Controller verwenden die Instanz-Methoden, um Aktionen auf "eigene" Ressourcen zu beschränken.
 */
class AccessService
{
    public function __construct(
        private AuthService $auth,
        private PostRepository $posts,
        private CommentRepository $comments
    ) {}

    // ─────────────────────────────────────────────────────────────────────────────
    // Statische Guards – kompatibel zu usersOnly()/adminOnly()
    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * Erfordert einen eingeloggten Benutzer.
     * Bei fehlender Session-ID → Redirect auf /login.php mit Fehlermeldung.
     */
    public static function requireUser(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/login.php');
            exit();
        }
    }

    /**
     * Erfordert einen Admin.
     * - Ohne Login → Redirect auf /login.php
     * - Ohne Admin-Flag → Redirect auf /index.php
     */
    public static function requireAdmin(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/login.php');
            exit();
        }

        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Nicht erlaubt';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = \defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/index.php');
            exit();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Instanz-API für Controller/Views
    // ─────────────────────────────────────────────────────────────────────────────

    /** true, wenn aktueller User Admin ist. */
   public function isAdmin(): bool
    {
        // Falls AuthService die Methode anbietet, nutze sie
        if (method_exists($this->auth, 'currentUserIsAdmin')) {
            return (bool) $this->auth->currentUserIsAdmin();
        }

        // Fallback: Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['admin']);
    }

    // Liefert die ID des eingeloggten Users oder null (Fallback über $_SESSION). */
    public function currentUserId(): ?int
    {
        // Falls AuthService die Methode anbietet, nutze sie
        if (method_exists($this->auth, 'currentUserId')) {
            $id = $this->auth->currentUserId();
            return $id !== null ? (int)$id : null;
        }

        // Fallback: Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;
    }

    /**
     * Darf der aktuelle Benutzer den Post verwalten?
     * Admin: immer ja
     * Normaler User: nur eigene Posts
     */
    public function canManagePost(int $postId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $uid = $this->currentUserId();
        if ($uid === null) {
            return false;
        }

        $post = $this->posts->findById($postId);
        if (!$post) {
            return false;
        }

        return (int)($post['user_id'] ?? 0) === $uid;
    }

    /**
     * Darf der aktuelle Benutzer den Kommentar verwalten?
     * Varianten:
     *  A) nur eigene Kommentare
     *  B) zusätzlich Kommentare unter eigenen Posts (moderationsähnlich)
     * Hier: A + B (üblich für Autoren).
     */
    public function canManageComment(int $commentId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $uid = $this->currentUserId();
        if ($uid === null) {
            return false;
        }

        $comment = $this->comments->findById($commentId);
        if (!$comment) {
            return false;
        }

        // Eigener Kommentar?
        if ((int)($comment['user_id'] ?? 0) === $uid) {
            return true;
        }

        // Kommentar unter eigenem Post?
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
