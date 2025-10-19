<?php
namespace App\OOP\Repositories;

use App\OOP\Core\LegacyDB;
use mysqli_stmt;
use const MYSQLI_ASSOC;

class DbRepository {
    public function executeQuery(string $sql, array $data): mysqli_stmt {
        $conn = LegacyDB::conn();
        $stmt = $conn->prepare($sql);
        if (!$stmt) die('Database prepare error: ' . $conn->error);
        if (!empty($data)) {
            $values = array_values($data);
            $types  = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        return $stmt;
    }
    public function selectAll(string $table, array $conditions = [], string $orderBy = ''): array {
        $sql = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $k => $_) $where[] = "$k=?";
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($orderBy !== '') $sql .= " ORDER BY $orderBy";
        $stmt = $this->executeQuery($sql, $conditions);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function selectOne(string $table, array $conditions = [], string $orderBy = ''): ?array {
        $sql = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $k => $_) $where[] = "$k=?";
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($orderBy !== '') $sql .= " ORDER BY $orderBy";
        $sql .= " LIMIT 1";
        $stmt = $this->executeQuery($sql, $conditions);
        $row  = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }
    public function create(string $table, array $data): int {
        $sql = "INSERT INTO $table SET " . implode(', ', array_map(fn($k)=>"$k=?", array_keys($data)));
        $stmt = $this->executeQuery($sql, $data);
        return $stmt->insert_id;
    }
    public function update(string $table, int $id, array $data): int {
        $sql = "UPDATE $table SET " . implode(', ', array_map(fn($k)=>"$k=?", array_keys($data))) . " WHERE id=?";
        $dataPlus = $data; $dataPlus['id'] = $id;
        $stmt = $this->executeQuery($sql, $dataPlus);
        return $stmt->affected_rows;
    }
    public function delete(string $table, int $id): int {
        $stmt = $this->executeQuery("DELETE FROM $table WHERE id=?", ['id'=>$id]);
        return $stmt->affected_rows;
    }
    public function getPublishedPosts(): array {
        $sql = "SELECT p.*, u.username FROM posts AS p
                JOIN users AS u ON p.user_id=u.id
                WHERE p.published=? ORDER BY p.created_at DESC";
        $stmt = $this->executeQuery($sql, ['published'=>1]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function getPostsByTopicId($topic_id): array {
        $sql = "SELECT p.*, u.username FROM posts AS p
                JOIN users AS u ON p.user_id=u.id
                WHERE p.published=? AND topic_id=? ORDER BY p.created_at DESC";
        $stmt = $this->executeQuery($sql, ['published'=>1, 'topic_id'=>$topic_id]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function searchPosts(string $term): array {
        $match = '%'.$term.'%';
        $sql = "SELECT p.*, u.username
                  FROM posts AS p
                  JOIN users AS u ON p.user_id=u.id
                 WHERE p.published=? AND (p.title LIKE ? OR p.body LIKE ?)
              ORDER BY p.created_at DESC";
        $stmt = $this->executeQuery($sql, ['published'=>1, $match, $match]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchCommentsForPost(int $postId, ?int $parentId): array {
        $sql  = "SELECT c.* FROM comments AS c WHERE c.post_id = ?";
       $data = [$postId];
        if ($parentId !== null) { $sql .= " AND c.parent_id = ?"; $data[] = $parentId; }
        $sql .= " ORDER BY c.created_at DESC";
        $stmt = $this->executeQuery($sql, $data);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
