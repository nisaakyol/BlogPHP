<?php
declare(strict_types=1);

// Zweck: Ermöglicht das Einreichen (Submit) eines eigenen Posts zur Moderation inkl. Besitzprüfung, Status-Update und Admin-Benachrichtigung.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\DbRepository;

final class PostWriteController
{
    // Repository-Injection für DB-Operationen
    public function __construct(private DbRepository $repo) {}

    // Post zur Moderation einreichen
    public function submit(int $postId): void
    {
        usersOnly(); // Zugriffsschutz: nur eingeloggte Nutzer

        // Aktuellen Nutzer aus der Session bestimmen
        $userId   = (int)($_SESSION['id'] ?? 0);
        $username = (string)($_SESSION['username'] ?? 'user');

        // 1) Post laden & Besitz prüfen
        $post = $this->repo->selectOne('posts', ['id' => $postId]);
        if (!$post || (int)$post['user_id'] !== $userId) {
            $_SESSION['message'] = 'Du kannst nur deine eigenen Posts einreichen.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
            exit;
        }

        // 2) Status auf "submitted" setzen (idempotent)
        $this->repo->submitPost($postId, $userId);

        // 3) Admin-Mail sicher auslösen (Helper laden + senden)
        require_once ROOT_PATH . '/app/Support/helpers/send-email.php';

        $ok = send_admin_mail([
            'type'     => 'moderation',
            'post_id'  => $postId,
            'title'    => (string)($post['title'] ?? ''),
        ]);

        // 4) Rückmeldung an den User
        if ($ok) {
            $_SESSION['message'] = 'Beitrag eingereicht. Admin wurde benachrichtigt.';
            $_SESSION['type']    = 'success';
        } else {
            $_SESSION['message'] = 'Beitrag eingereicht, aber die Admin-E-Mail konnte nicht gesendet werden.';
            $_SESSION['type']    = 'error';
        }

        // 5) Zurück ins Dashboard
        header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts');
        exit;
    }
}
