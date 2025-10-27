<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class PostWriteController
{
    public function __construct(private DbRepository $repo) {}

   // App\OOP\Controllers\PostWriteController
    public function submit(int $postId): void
    {
        usersOnly();

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

        // 2) Status auf "submitted" setzen (wenn schon submitted, macht's nichts kaputt)
        $this->repo->submitPost($postId, $userId);

        // 3) Admin-Mail SICHER auslösen (Helper laden + senden)
        require_once ROOT_PATH . '/app/helpers/send-email.php';

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
            // Falls mal was hakt, sieht der User eine deutliche Meldung
            $_SESSION['message'] = 'Beitrag eingereicht, aber die Admin-E-Mail konnte nicht gesendet werden.';
            $_SESSION['type']    = 'error';
        }

        // 5) Zurück ins Dashboard
        header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts');
        exit;
    }
}
