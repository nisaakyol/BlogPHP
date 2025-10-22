<?php
declare(strict_types=1);

namespace App\OOP\Repositories;

use App\OOP\Core\DB;
use PDOStatement;

/**
 * CommentRepository
 *
 * Schreibt Kommentare zur Datenbank. parent_id kann NULL sein (Threading).
 */
class CommentRepository
{
    /**
     * Legt einen Kommentar an. $parentId darf null sein.
     *
     * @param string   $username  Anzeigename des Kommentierenden
     * @param string   $comment   Kommentartext
     * @param int|null $parentId  Eltern-Kommentar-ID oder null (Root-Kommentar)
     * @param int      $postId    Ziel-Post-ID
     *
     * @return bool true bei Erfolg, sonst false
     */
    public static function create(string $username, string $comment, ?int $parentId, int $postId): bool
    {
        $sql = 'INSERT INTO comments (username, comment, parent_id, post_id)
                VALUES (:u, :c, :pid, :post)';

        $st = DB::pdo()->prepare($sql);

        return $st->execute([
            ':u'    => $username,
            ':c'    => $comment,
            ':pid'  => $parentId, // PDO setzt NULL korrekt
            ':post' => $postId,
        ]);
    }
    public function listByAuthor(int $userId): array {
    return $this->db->query("SELECT * FROM posts WHERE user_id = {$userId} ORDER BY created_at DESC")->fetchAll();
    }

    public function listCommentsByUser(int $userId): array {
        return $this->db->query("SELECT * FROM comments WHERE user_id = {$userId} ORDER BY created_at DESC")->fetchAll();
    }

}
