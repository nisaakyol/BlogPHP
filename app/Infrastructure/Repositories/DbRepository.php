<?php
declare(strict_types=1);

// Zweck: Zentrales DB-Repository (generisches CRUD, Auth-Helfer, Post-Workflow, Suche inkl. Fuzzy-Re-Ranking, Kommentar-Operationen)

namespace App\Infrastructure\Repositories;

require_once ROOT_PATH . '/app/Infrastructure/Core/DB.php';
require_once ROOT_PATH . '/app/Infrastructure/Services/SearchService.php';

use App\Infrastructure\Core\DB;
use PDO;

final class DbRepository
{
    // PDO-Handle (zentrale Instanz aus Core\DB)
    private PDO $pdo;

    public function __construct()
    {
        // holt die eine zentrale PDO-Instanz
        $this->pdo = DB::pdo();
    }

    // selectAll('posts', ['published'=>1], 'created_at DESC', 10)
    // Rückgabe: array<int,array<string,mixed>>
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

        // optionale Spalten ermitteln
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

        // keine Bindung für NOW()
        $params = array_intersect_key(
            $data,
            array_flip(array_filter($columns, fn($c) => $c !== 'created_at' && $c !== 'updated_at'))
        );
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

    // Nur veröffentlichte/approved Posts für Frontend
    public function getPublishedPosts(): array
    {
        $hasPubAt = $this->columnExists('posts', 'published_at');
        $hasRevAt = $this->columnExists('posts', 'reviewed_at');

        $orderExpr = 'p.created_at';
        if ($hasPubAt || $hasRevAt) {
            // Fallback-Kaskade: published_at > reviewed_at > created_at
            $orderExpr = 'COALESCE(p.published_at, p.reviewed_at, p.created_at)';
        }

        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE (p.published = 1 OR p.status = 'approved')
                ORDER BY {$orderExpr} DESC, p.id DESC";
        $st = $this->pdo->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function submitPost(int $postId, int $userId): int
    {
        $sql = "UPDATE posts
                SET status = 'submitted',
                    published = 0,
                    reviewed_at = NULL,
                    reviewer_id = NULL,
                    review_note = NULL
                WHERE id = :id AND user_id = :uid";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $postId, ':uid' => $userId]);
        return $st->rowCount();
    }

    public function approvePost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $setPubAt = $this->columnExists('posts', 'published_at') ? ', published_at = NOW()' : '';
        $sql = "UPDATE posts
                SET status = 'approved',
                    published = 1,
                    reviewer_id = :rid,
                    reviewed_at = NOW(),
                    review_note = :note
                    {$setPubAt}
                WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->execute([':rid' => $reviewerId, ':note' => $note, ':id' => $postId]);
        return $st->rowCount();
    }

    public function rejectPost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $sql = "UPDATE posts
                SET status = 'rejected',
                    published = 0,
                    reviewer_id = :rid,
                    reviewed_at = NOW(),
                    review_note = :note
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

    // Volltext-/LIKE-Suche mit Fuzzy-Reranking (nur veröffentlichte/approved)
    public function searchPosts(string $term): array
    {
        $term = trim($term);
        if ($term === '') return [];

        $NEEDED_MIN     = 3;     // zu wenige Kandidaten → Fallback
        $CANDIDATE_LIMIT = 500;  // Obergrenze

        $rows = [];

        // 1) Kandidaten via FULLTEXT oder LIKE
        if ($this->hasFulltextPosts()) {
            $sql = "SELECT p.id, p.title, p.body, p.created_at, u.username,
                           COALESCE(p.image,'') AS image,
                           MATCH(p.title, p.body) AGAINST (:q1 IN NATURAL LANGUAGE MODE) AS ft_score
                    FROM posts p
                    JOIN users u ON u.id = p.user_id
                    WHERE (p.published = 1 OR p.status = 'approved')
                      AND MATCH(p.title, p.body) AGAINST (:q2 IN NATURAL LANGUAGE MODE)
                    ORDER BY ft_score DESC
                    LIMIT {$CANDIDATE_LIMIT}";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':q1', $term, PDO::PARAM_STR);
            $st->bindValue(':q2', $term, PDO::PARAM_STR);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $like = '%' . $term . '%';
            $sql = "SELECT p.id, p.title, p.body, p.created_at, u.username,
                           COALESCE(p.image,'') AS image
                    FROM posts p
                    JOIN users u ON u.id = p.user_id
                    WHERE (p.published = 1 OR p.status = 'approved')
                      AND (p.title LIKE :q1 OR p.body LIKE :q2)
                    ORDER BY p.created_at DESC
                    LIMIT {$CANDIDATE_LIMIT}";
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':q1', $like, PDO::PARAM_STR);
            $st->bindValue(':q2', $like, PDO::PARAM_STR);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2) Fallback: zu wenige Kandidaten → letzte X Posts holen
        if (count($rows) < $NEEDED_MIN) {
            $sql = "SELECT p.id, p.title, p.body, p.created_at, u.username,
                           COALESCE(p.image,'') AS image
                    FROM posts p
                    JOIN users u ON u.id = p.user_id
                    WHERE (p.published = 1 OR p.status = 'approved')
                    ORDER BY p.created_at DESC
                    LIMIT {$CANDIDATE_LIMIT}";
            $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3) Re-Ranking (Fuzzy + optional FULLTEXT-Bonus)
        $scored = [];
        foreach ($rows as $r) {
            $score = \App\Infrastructure\Services\SearchService::score(
                $term,
                (string)($r['title'] ?? ''),
                (string)($r['body']  ?? ''),
                $r['created_at'] ?? null
            );
            if (isset($r['ft_score'])) $score += min(20.0, (float)$r['ft_score']);

            if ($score >= 10.0) {
                $r['_score'] = $score;
                $scored[] = $r;
            }
        }

        // 4) Sortierung: Relevanz DESC, dann Datum DESC, dann ID DESC
        usort($scored, function ($a, $b) {
            if ($a['_score'] == $b['_score']) {
                $ad = $a['created_at'] ?? '1970-01-01 00:00:00';
                $bd = $b['created_at'] ?? '1970-01-01 00:00:00';
                if ($ad === $bd) return ($b['id'] <=> $a['id']);
                return strcmp($bd, $ad);
            }
            return $b['_score'] <=> $a['_score'];
        });

        // Hilfsfelder entfernen
        foreach ($scored as &$r) { unset($r['_score'], $r['ft_score']); }

        return $scored;
    }

    // Prüft, ob FULLTEXT-Index vorhanden ist
    private function hasFulltextPosts(): bool
    {
        $sql = "SELECT 1
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'posts'
                  AND INDEX_NAME = 'ft_posts_title_body'
                LIMIT 1";
        $st = $this->pdo->query($sql);
        return (bool)$st->fetchColumn();
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

    // Einzelnen Post fürs Frontend (nur veröffentlichte/approved)
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
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Kommentare zu einem Post (inkl. robustem Autor-Feld)
    // Rückgabe: array<int, array<string,mixed>>
    public function fetchCommentsForPost(int $postId): array
    {
        $hasUserId = $this->columnExists('comments', 'user_id');

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

        if ($hasUserId) {
            $sql = "SELECT c.*, u.username AS author
                    FROM comments c
                    JOIN users u ON u.id = c.user_id
                    WHERE c.post_id = :pid
                    ORDER BY c.{$orderCol} ASC, c.id ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':pid' => $postId]);
        } else {
            $authorExpr = $authorCol ? "c.`{$authorCol}`" : "NULL";
            $sql = "SELECT c.*, {$authorExpr} AS author
                    FROM comments c
                    WHERE c.post_id = :pid
                    ORDER BY c.{$orderCol} ASC, c.id ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':pid' => $postId]);
        }

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tabellen-Existenz prüfen
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

    // Spalten-Existenz prüfen
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

    // Neuen Kommentar anlegen (eingeloggter User oder Fallback auf comments.username)
    public function createComment(int $postId, int $userId, string $text, ?int $parentId = null): int
    {
        $hasUserId   = $this->columnExists('comments', 'user_id');
        $hasUsername = $this->columnExists('comments', 'username');

        $tsCol = null;
        foreach (['created_at','created','timestamp','date'] as $c) {
            if ($this->columnExists('comments', $c)) { $tsCol = $c; break; }
        }
        $hasParent = $this->columnExists('comments', 'parent_id');

        $data = [
            'post_id' => $postId,
            'comment' => $text,
        ];
        if ($hasUserId)   $data['user_id']  = $userId;
        if (!$hasUserId && $hasUsername)    $data['username'] = $_SESSION['username'] ?? 'user';
        if ($hasParent && $parentId !== null) $data['parent_id'] = $parentId;

        $cols  = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);

        if ($tsCol) { $cols[] = $tsCol; $place[] = 'NOW()'; }

        $sql = 'INSERT INTO comments (' . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
        $st  = $this->pdo->prepare($sql);

        $params = array_intersect_key($data, array_flip(array_filter($cols, fn($c) => $c !== $tsCol)));
        $st->execute($params);

        return (int)$this->pdo->lastInsertId();
    }

    // Kommentar für Gäste anlegen (Username wird in comments.* gespeichert)
    public function createCommentGuest(int $postId, string $username, string $text, ?int $parentId = null): int
    {
        $hasParent = $this->columnExists('comments', 'parent_id');

        $authorCol = null;
        foreach (['username', 'name', 'author'] as $c) {
            if ($this->columnExists('comments', $c)) { $authorCol = $c; break; }
        }

        $tsCol = null;
        foreach (['created_at', 'created', 'timestamp', 'date'] as $c) {
            if ($this->columnExists('comments', $c)) { $tsCol = $c; break; }
        }

        $data = [
            'post_id' => $postId,
            'comment' => $text,
        ];
        if ($authorCol)                 $data[$authorCol] = $username;
        if ($hasParent && $parentId)    $data['parent_id'] = $parentId;

        $cols  = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);

        if ($tsCol) { $cols[] = $tsCol; $place[] = 'NOW()'; }

        $sql = 'INSERT INTO comments (' . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
        $st  = $this->pdo->prepare($sql);

        $params = array_intersect_key($data, array_flip(array_filter($cols, fn($c) => $c !== $tsCol)));
        $st->execute($params);

        return (int)$this->pdo->lastInsertId();
    }

    public function popularPostsByComments(int $days = 30, int $limit = 5): array
    {
        $sql = "SELECT p.*, COUNT(c.id) AS comment_count
                FROM posts p
                LEFT JOIN comments c 
                ON c.post_id = p.id
                AND c.created_at >= (NOW() - INTERVAL :days DAY)
                WHERE p.published = 1
                GROUP BY p.id
                ORDER BY comment_count DESC, p.created_at DESC
                LIMIT :limit";

        $pdo = \App\Infrastructure\Core\DB::pdo();
        $st  = $pdo->prepare($sql);
        $st->bindValue(':days',  $days,  \PDO::PARAM_INT);
        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll();
    }

}
