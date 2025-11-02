<?php
declare(strict_types=1);

// Datei: app/Infrastructure/Repositories/CommentRepository.php
// Zweck: Datenzugriff für Kommentare (Gäste & eingeloggte Nutzer): anlegen und nach Post laden.

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Core\DB;
use PDO;

final class CommentRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        // PDO aus Core holen, wenn nicht injiziert
        $this->pdo = $pdo ?? DB::pdo();
    }

    // Legt einen Kommentar für Gäste an (Schema ohne user_id)
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

        return (int) $this->pdo->lastInsertId();
    }

    // Optional: identisch für eingeloggte User (Name kommt aus Session/Controller)
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

        return (int) $this->pdo->lastInsertId();
    }

    // Lädt alle Kommentare zu einem Post (inkl. potenzieller Replies), ohne Status-Filter
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
