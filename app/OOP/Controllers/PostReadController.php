<?php
namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

class PostReadController
{
    public function __construct(private DbRepository $db) {}

    public function show(int $id): array
    {
        $post = $this->db->selectOne('posts', ['id' => $id]);
        if (!$post || (int)$post['published'] !== 1) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Username joinen (gleiches Verhalten)
        $user = $this->db->selectOne('users', ['id' => $post['user_id']]);
        $post['username'] = $user['username'] ?? 'unknown';

        // Comments
        $comments = $this->db->fetchCommentsForPost($id, null) ?? [];

        return compact('post', 'comments');
    }
}
