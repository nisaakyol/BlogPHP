<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

class CommentController
{
    public function __construct(private DbRepository $db) {}

    /** Speichert Kommentar + Redirect zurück auf den Post */
    public function store(array $data): void
    {
        // Honeypot optional
        if (!empty($data['honeypot'] ?? '')) {
            header('Location: ' . BASE_URL . '/index.php'); exit;
        }

        $postId   = (int)($data['post_id']  ?? 0);
        $parentId = trim((string)($data['parent_id'] ?? ''));
        $username = trim((string)($data['username']  ?? ''));
        $comment  = trim((string)($data['comment']   ?? ''));

        $errors = [];
        if ($postId <= 0)     $errors[] = 'Ungültige Post-ID.';
        if ($username === '') $errors[] = 'Bitte Benutzernamen eingeben.';
        if ($comment === '')  $errors[] = 'Bitte Kommentartext eingeben.';

        if ($errors) {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = [
                'username'  => $username,
                'comment'   => $comment,
                'parent_id' => $parentId,
            ];
            header('Location: ' . BASE_URL . '/single.php?id=' . $postId . '#comments');
            exit;
        }

        $this->db->createComment([
            'post_id'   => $postId,
            'parent_id' => $parentId, // '' oder ID
            'username'  => $username,
            'comment'   => $comment,
        ]);

        header('Location: ' . BASE_URL . '/single.php?id=' . $postId . '#comments');
        exit;
    }

    /** Lädt Kommentare rekursiv (nutzt dein fetchCommentsForPost) */
    public function treeForPost(int $postId): array
    {
        $build = function (?int $parentId) use (&$build, $postId): array {
            $rows = $this->db->fetchCommentsForPost($postId, $parentId);
            foreach ($rows as &$r) {
                $r['children'] = $build((int)$r['id']);
            }
            return $rows;
        };
        return $build(null);
    }
}
