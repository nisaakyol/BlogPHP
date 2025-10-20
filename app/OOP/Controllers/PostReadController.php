<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

/**
 * PostReadController
 *
 * Lädt einen einzelnen veröffentlichten Post inkl. Username und zugehöriger Kommentare.
 * Nicht veröffentlichte oder nicht existente Posts leiten auf die Startseite um.
 */
class PostReadController
{
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Zeigt einen Post, wenn veröffentlicht, sonst Redirect.
     *
     * @param int $id Post-ID
     * @return array{post: array, comments: array}
     */
    public function show(int $id): array
    {
        // Post laden
        $post = $this->db->selectOne('posts', ['id' => $id]);
        if (!$post || (int) ($post['published'] ?? 0) !== 1) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Username zum Post „joinen“ (gleiches Verhalten)
        $user              = $this->db->selectOne('users', ['id' => (int) $post['user_id']]);
        $post['username']  = $user['username'] ?? 'unknown';

        // Kommentare laden (Root-Ebene)
        $comments = $this->db->fetchCommentsForPost($id, null) ?? [];

        return compact('post', 'comments');
    }
}
