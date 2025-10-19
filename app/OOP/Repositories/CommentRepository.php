<?php
namespace App\OOP\Repositories;

use App\OOP\Core\DB;

class CommentRepository {
    /**
     * Legt einen Kommentar an. $parentId darf null sein.
     */
    public static function create(string $username, string $comment, ?int $parentId, int $postId): bool {
        $sql = "INSERT INTO comments (username, comment, parent_id, post_id)
                VALUES (:u, :c, :pid, :post)";
        $st = DB::pdo()->prepare($sql);
        return $st->execute([
            ':u'   => $username,
            ':c'   => $comment,
            ':pid' => $parentId,        // PDO setzt NULL korrekt
            ':post'=> $postId,
        ]);
    }
}
