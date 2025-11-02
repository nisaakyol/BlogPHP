<?php
declare(strict_types=1);

// Zweck: Lädt einen einzelnen veröffentlichten Post inklusive Autorname und Root-Kommentaren und leitet sonst zur Startseite um.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\DbRepository;

class PostReadController
{
    // Repository via Constructor-Injection
    public function __construct(private DbRepository $db)
    {
    }

    public function show(int $id): array
    {
        // Post per ID laden
        $post = $this->db->selectOne('posts', ['id' => $id]);

        // Wenn nicht vorhanden oder nicht veröffentlicht → zurück zur Startseite
        if (!$post || (int) ($post['published'] ?? 0) !== 1) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Autor zum Post "joinen" (einfacher Lookup)
        $user             = $this->db->selectOne('users', ['id' => (int) $post['user_id']]);
        $post['username'] = $user['username'] ?? 'unknown';

        // Root-Kommentare zum Post laden (parent_id = null)
        $comments = $this->db->fetchCommentsForPost($id, null) ?? [];

        // Kompaktes View-Model zurückgeben
        return compact('post', 'comments');
    }
}
