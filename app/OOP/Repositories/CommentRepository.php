<?php
declare(strict_types=1);

namespace App\OOP\Repositories;

use App\OOP\Core\DB;
use PDO;

/**
 * CommentRepository
 *
 * Verwaltet das Schreiben/Lesen von Kommentaren.
 * Kompatibel zu Tabelle:
 *   comments(id, parent_id, post_id, username, comment, created_at)
 */
final class CommentRepository
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    /**
     * Legt einen Kommentar für GÄSTE an (kein user_id-Feld in der Tabelle).
     *
     * @param int        $postId
     * @param string     $username  Anzeigename (Pflicht bei Gästen)
     * @param string     $comment   Kommentartext
     * @param int|null   $parentId  Eltern-Kommentar-ID oder null (Root)
     *
     * @return int  Neue Kommentar-ID
     */
    public function createGuest(int $postId, string $username, string $comment, ?int $parentId = null): int
    {
        $sql = "INSERT INTO comments (parent_id, post_id, username, comment, created_at)
                VALUES (:pid, :post, :u, :c, NOW())";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':pid'  => $parentId,
            ':post' => $postId,
            ':u'    => $username,
            ':c'    => $comment,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * OPTIONAL: Für eingeloggte User – wenn du den Namen aus Session verwendest,
     * kannst du diese Methode ebenfalls nutzen (gleiches Schema).
     * Sie ist semantisch identisch zu createGuest, dient nur der Klarheit.
     */
    public function createForUser(int $postId, string $username, string $comment, ?int $parentId = null): int
    {
        $sql = "INSERT INTO comments (parent_id, post_id, username, comment, created_at)
                VALUES (:pid, :post, :u, :c, NOW())";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':pid'  => $parentId,
            ':post' => $postId,
            ':u'    => $username,
            ':c'    => $comment,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Kommentare eines Posts laden (inkl. Replies), ohne Status-Filter
     * (es gibt keine status-Spalte im aktuellen Schema).
     *
     * @return array<int,array<string,mixed>>
     */
    public function findByPost(int $postId): array
    {
        $sql = "SELECT id, parent_id, post_id, username, comment, created_at
                FROM comments
                WHERE post_id = :post
                ORDER BY created_at ASC, id ASC";

        $st = $this->pdo->prepare($sql);
        $st->execute([':post' => $postId]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
