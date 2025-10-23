<?php
declare(strict_types=1);

namespace App\OOP\Repositories;

use App\OOP\Core\DB;
use PDO;

final class DbRepository
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct()
    {
        // holt die eine zentrale PDO-Instanz
        $this->pdo = DB::pdo();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Legacy-kompatible API
    // ─────────────────────────────────────────────────────────────────────

    /**
     * selectAll('posts', ['published'=>1], 'created_at DESC', 10)
     *
     * @return array<int,array<string,mixed>>
     */
    public function selectAll(
        string $table,
        array $conditions = [],
        ?string $orderBy = null,
        ?int $limit = null
    ): array {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$table}{$whereSql}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit !== null) $sql .= " LIMIT " . (int)$limit;

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectOne(string $table, array $conditions): ?array
    {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$table}{$whereSql} LIMIT 1";
        $st  = $this->pdo->prepare($sql);
        $st->execute($params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $table, array $data): int
    {
        $cols  = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);
        $sql   = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $place) . ")";
        $st    = $this->pdo->prepare($sql);
        $st->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, int $id, array $data): int
    {
        $sets = [];
        foreach ($data as $k => $v) $sets[] = "{$k} = :{$k}";
        $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE id = :__id";
        $data['__id'] = $id;

        $st = $this->pdo->prepare($sql);
        $st->execute($data);
        return $st->rowCount();
    }

    public function delete(string $table, int $id): int
    {
        $st = $this->pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $st->execute([$id]);
        return $st->rowCount();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Spezielle Helfer für Auth
    // ─────────────────────────────────────────────────────────────────────

    public function findUserByUsernameOrEmail(string $username, string $email): ?array
    {
        $sql = "SELECT id, username, email FROM users
                WHERE username = :u OR email = :e
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':u' => $username, ':e' => $email]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createUser(string $username, string $email, string $passwordHash): int
    {
        $pdo = $this->pdo;

        $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
        $hasCreated = in_array('created_at', $cols, true);
        $hasUpdated = in_array('updated_at', $cols, true);
        $hasAdmin   = in_array('admin',      $cols, true);

        $data = [
            'username' => $username,
            'email'    => $email,
            'password' => $passwordHash,
        ];
        if ($hasAdmin) { $data['admin'] = 0; }

        $columns = array_keys($data);
        $place   = array_map(fn($c) => ':' . $c, $columns);

        if ($hasCreated) { $columns[] = 'created_at'; $place[] = 'NOW()'; }
        if ($hasUpdated) { $columns[] = 'updated_at'; $place[] = 'NOW()'; }

        $sql = 'INSERT INTO users (' . implode(',', $columns) . ') VALUES (' . implode(',', $place) . ')';
        $st  = $pdo->prepare($sql);

        $params = array_intersect_key($data, array_flip(array_filter($columns, fn($c) => $c !== 'created_at' && $c !== 'updated_at')));
        $st->execute($params);

        return (int)$pdo->lastInsertId();
    }

    public function findUserByIdentifier(string $identifier): ?array
    {
        $sql = "SELECT * FROM users
                WHERE username = :u OR email = :e
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':u' => $identifier, ':e' => $identifier]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // intern
    // ─────────────────────────────────────────────────────────────────────

    private function buildWhere(array $conditions): array
    {
        if ($conditions === []) return ['', []];

        $parts  = [];
        $params = [];
        foreach ($conditions as $k => $v) {
            $param           = ':' . $k;
            $parts[]         = "{$k} = {$param}";
            $params[$param]  = $v;
        }
        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    // Nur "approved"/veröffentlichte Posts für Frontend
    public function getPublishedPosts(): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.status = 'approved'
                ORDER BY p.created_at DESC";
        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function submitPost(int $postId, int $userId): int
    {
        $sql = "UPDATE posts
                SET status = 'submitted', published = 0, reviewed_at = NULL, reviewer_id = NULL, review_note = NULL
                WHERE id = :id AND user_id = :uid";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $postId, ':uid' => $userId]);
        return $st->rowCount();
    }

    public function approvePost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $sql = "UPDATE posts
                SET status = 'approved', published = 1, reviewer_id = :rid, reviewed_at = NOW(), review_note = :note
                WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reviewerId, ':note' => $note, ':id' => $postId]);
        return $st->rowCount();
    }

    public function rejectPost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $sql = "UPDATE posts
                SET status = 'rejected', published = 0, reviewer_id = :rid, reviewed_at = NOW(), review_note = :note
                WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reviewerId, ':note' => $note, ':id' => $postId]);
        return $st->rowCount();
    }

    public function listByStatus(string $status): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.status = :s
                ORDER BY p.created_at DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':s' => $status]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostsByTopicId(int $topicId): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.published = 1
                  AND p.topic_id = :tid
                ORDER BY p.created_at DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':tid' => $topicId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Volltextsuche (LIKE) – nur veröffentlichte/approved
    public function searchPosts(string $term): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE (p.title LIKE :q_title OR p.body LIKE :q_body)
                AND (p.published = 1 OR p.status = 'approved')
                ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $q = '%' . $term . '%';
        $stmt->execute([
            ':q_title' => $q,
            ':q_body'  => $q,
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function deleteCommentsByPost(int $postId): int
    {
        $st = $this->pdo->prepare('DELETE FROM comments WHERE post_id = :id');
        $st->execute([':id' => $postId]);
        return $st->rowCount();
    }

    public function deletePostTopics(int $postId): int
    {
        if (!$this->tableExists('post_topic')) return 0;
        $st = $this->pdo->prepare('DELETE FROM post_topic WHERE post_id = :id');
        $st->execute([':id' => $postId]);
        return $st->rowCount();
    }

    public function deletePost(int $id): int
    {
        $st = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->rowCount();
    }

    // Einzelnen Post fürs Frontend laden (nur veröffentlichte/approved)
    public function fetchPostPublic(int $id): ?array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.id = :id
                AND (p.published = 1 OR p.status = 'approved')
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $id]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Kommentare zu einem Post laden (inkl. Autor-Name)
     * Rückgabe: array<int, array<string,mixed>>
     */
    public function fetchCommentsForPost(int $postId): array
    {
        // 1) Verfügbare Spalten ermitteln
        $hasUserId   = $this->columnExists('comments', 'user_id');

        // mögliche Autor-Spalten direkt in comments
        $authorCandidates = ['username', 'name', 'author'];
        $authorCol = null;
        foreach ($authorCandidates as $c) {
            if ($this->columnExists('comments', $c)) { $authorCol = $c; break; }
        }

        // verfügbare Sortierspalte bestimmen
        $orderCandidates = ['created_at', 'created', 'createdOn', 'date', 'timestamp', 'id'];
        $orderCol = 'id';
        foreach ($orderCandidates as $c) {
            if ($this->columnExists('comments', $c)) { $orderCol = $c; break; }
        }

        // 2) Query je nach Schema bauen
        if ($hasUserId) {
            // JOIN auf users, Username von dort
            $sql = "SELECT c.*, u.username AS author
                    FROM comments c
                    JOIN users u ON u.id = c.user_id
                    WHERE c.post_id = :pid
                    ORDER BY c.{$orderCol} ASC, c.id ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':pid' => $postId]);
        } else {
            // Kein user_id: Autor kommt aus comments.<username|name|author> (falls vorhanden)
            // author-Feld robust befüllen (sonst NULL)
            $authorExpr = $authorCol ? "c.`{$authorCol}`" : "NULL";
            $sql = "SELECT c.*, {$authorExpr} AS author
                    FROM comments c
                    WHERE c.post_id = :pid
                    ORDER BY c.{$orderCol} ASC, c.id ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':pid' => $postId]);
        }

    return $st->fetchAll(\PDO::FETCH_ASSOC);
    }
    // ersetzt die alte tableExists()
    private function tableExists(string $table): bool
    {
        $sql = "SELECT 1
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = :t
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':t' => $table]);
        return (bool) $st->fetchColumn();
    }

    // ersetzt die alte columnExists()
    private function columnExists(string $table, string $column): bool
    {
        $sql = "SELECT 1
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = :t
                AND COLUMN_NAME  = :c
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':t' => $table, ':c' => $column]);
        return (bool) $st->fetchColumn();
    }
    // Neuen Kommentar anlegen
    public function createComment(int $postId, int $userId, string $text, ?int $parentId = null): int
    {
        // Welche Spalten existieren?
        $hasUserId   = $this->columnExists('comments', 'user_id');
        $hasUsername = $this->columnExists('comments', 'username');
        $tsCol = null;
        foreach (['created_at','created','timestamp','date'] as $c) {
            if ($this->columnExists('comments', $c)) { $tsCol = $c; break; }
        }
        $hasParent = $this->columnExists('comments', 'parent_id');

        // Daten-Array dynamisch aufbauen
        $data = [
            'post_id' => $postId,
            'comment' => $text,
        ];
        if ($hasUserId)   $data['user_id']  = $userId;
        if (!$hasUserId && $hasUsername) {
            // Fallback: Username direkt in comments.username speichern
            $data['username'] = $_SESSION['username'] ?? 'user';
        }
        if ($hasParent && $parentId !== null) $data['parent_id'] = $parentId;

        // Spalten + Platzhalter
        $cols  = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);

        // optionalen Zeitstempel als NOW() anhängen
        if ($tsCol) {
            $cols[]  = $tsCol;
            $place[] = 'NOW()';
        }

        $sql = 'INSERT INTO comments (' . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
        $st  = $this->pdo->prepare($sql);

        // Nur benannte Parameter binden (NOW() hat keinen)
        $params = array_intersect_key($data, array_flip(array_filter($cols, fn($c) => $c !== $tsCol)));
        $st->execute($params);

        return (int)$this->pdo->lastInsertId();
    }


}
