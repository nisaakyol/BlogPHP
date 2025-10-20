<?php
declare(strict_types=1);

namespace App\OOP\Repositories;

use App\OOP\Core\LegacyDB;
use mysqli_stmt;

use const MYSQLI_ASSOC;

/**
 * DbRepository
 *
 * Dünne Wrapper um die Legacy-mysqli-Schicht:
 * - Prepared Statements über executeQuery()
 * - CRUD-Helper (selectAll/selectOne/create/update/delete)
 * - Blog-spezifische Reader (Published Posts, Posts by Topic, Suche, Comments)
 *
 * Hinweise:
 * - $table/$orderBy werden unverändert in SQL übernommen (wie im Legacy-Code).
 * - Parameterbindung erfolgt als Strings ('s') – Legacy-kompatibel.
 * - get_result() erfordert mysqlnd.
 */
class DbRepository
{
    /**
     * Führt ein Prepared Statement aus und bindet alle Parameter (als 's').
     *
     * @param string $sql   SQL mit Platzhaltern (?)
     * @param array  $data  Assoziatives oder numerisches Array der Parameterwerte
     * @return mysqli_stmt
     */
    public function executeQuery(string $sql, array $data): mysqli_stmt
    {
        $conn = LegacyDB::conn();
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die('Database prepare error: ' . $conn->error);
        }

        if (!empty($data)) {
            $values = array_values($data);
            $types  = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * SELECT * FROM $table [WHERE ...] [ORDER BY ...]
     */
    public function selectAll(string $table, array $conditions = [], string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$table}";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $k => $_) {
                $where[] = "{$k}=?";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->executeQuery($sql, $conditions);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * SELECT * FROM $table [WHERE ...] [ORDER BY ...] LIMIT 1
     */
    public function selectOne(string $table, array $conditions = [], string $orderBy = ''): ?array
    {
        $sql = "SELECT * FROM {$table}";

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $k => $_) {
                $where[] = "{$k}=?";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->executeQuery($sql, $conditions);
        $row  = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    /**
     * INSERT INTO $table SET col1=?, col2=?, ...
     *
     * @return int Insert-ID
     */
    public function create(string $table, array $data): int
    {
        $sql  = 'INSERT INTO ' . $table . ' SET ';
        $sql .= implode(', ', array_map(static fn ($k) => "{$k}=?", array_keys($data)));

        $stmt = $this->executeQuery($sql, $data);
        return $stmt->insert_id;
    }

    /**
     * UPDATE $table SET col1=?, ... WHERE id=?
     *
     * @return int Betroffene Zeilen
     */
    public function update(string $table, int $id, array $data): int
    {
        $sql  = 'UPDATE ' . $table . ' SET ';
        $sql .= implode(', ', array_map(static fn ($k) => "{$k}=?", array_keys($data)));
        $sql .= ' WHERE id=?';

        $dataPlus       = $data;
        $dataPlus['id'] = $id;

        $stmt = $this->executeQuery($sql, $dataPlus);
        return $stmt->affected_rows;
    }

    /**
     * DELETE FROM $table WHERE id=?
     *
     * @return int Betroffene Zeilen
     */
    public function delete(string $table, int $id): int
    {
        $stmt = $this->executeQuery("DELETE FROM {$table} WHERE id=?", ['id' => $id]);
        return $stmt->affected_rows;
    }

    /**
     * Veröffentliche Posts (inkl. Username), absteigend nach Erstellzeit.
     */
    public function getPublishedPosts(): array
    {
        $sql = "SELECT p.*, u.username
                  FROM posts AS p
                  JOIN users AS u ON p.user_id = u.id
                 WHERE p.published = ?
              ORDER BY p.created_at DESC";

        $stmt = $this->executeQuery($sql, ['published' => 1]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Veröffentliche Posts eines Topics (inkl. Username).
     */
    public function getPostsByTopicId($topic_id): array
    {
        $sql = "SELECT p.*, u.username
                  FROM posts AS p
                  JOIN users AS u ON p.user_id = u.id
                 WHERE p.published = ? AND topic_id = ?
              ORDER BY p.created_at DESC";

        $stmt = $this->executeQuery($sql, ['published' => 1, 'topic_id' => $topic_id]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Suche in Titel/Body (nur veröffentlichte Posts), inkl. Username.
     */
    public function searchPosts(string $term): array
    {
        $match = '%' . $term . '%';

        $sql = "SELECT p.*, u.username
                  FROM posts AS p
                  JOIN users AS u ON p.user_id = u.id
                 WHERE p.published = ?
                   AND (p.title LIKE ? OR p.body LIKE ?)
              ORDER BY p.created_at DESC";

        $stmt = $this->executeQuery($sql, ['published' => 1, $match, $match]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Kommentare zu einem Post (optional gefiltert nach parent_id), neueste zuerst.
     */
    public function fetchCommentsForPost(int $postId, ?int $parentId): array
    {
        $sql  = 'SELECT c.* FROM comments AS c WHERE c.post_id = ?';
        $data = [$postId];

        if ($parentId !== null) {
            $sql   .= ' AND c.parent_id = ?';
            $data[] = $parentId;
        }

        $sql  .= ' ORDER BY c.created_at DESC';

        $stmt = $this->executeQuery($sql, $data);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
