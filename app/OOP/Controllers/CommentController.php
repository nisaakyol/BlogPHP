<?php
namespace App\OOP\Controllers;

use App\OOP\Repositories\CommentRepository;

class CommentController {
    /**
     * 1:1 zu deiner bisherigen Datei:
     * - Liest POST-Felder
     * - Prüft Pflichtfelder
     * - Insert + Redirect single.php?id=...
     * - Gibt Fehlermeldungen wie zuvor aus
     */
    public function createFromPost(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return; // tut vorher auch nichts bei GET
        }

        $username  = $_POST['username']  ?? '';
        $comment   = $_POST['comment']   ?? '';
        $parentRaw = $_POST['parent_id'] ?? null;
        $postRaw   = $_POST['post_id']   ?? null;

        $parentId = ($parentRaw === '' || $parentRaw === null) ? null : (int)$parentRaw;
        $postId   = (int)$postRaw;

        if ($username !== '' && $comment !== '') {
            $ok = CommentRepository::create($username, $comment, $parentId, $postId);
            if ($ok) {
                header("Location: single.php?id=" . $postId);
                exit;
            }
            echo "Error: Insert failed."; // deine alte Datei echo't im Fehlerfall ebenfalls
        } else {
            echo "Please fill in all fields.";
        }
    }
}
