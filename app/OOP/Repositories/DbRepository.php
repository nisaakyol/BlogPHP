<?php
declare(strict_types=1);

namespace App\OOP\Repositories;

use App\OOP\Core\DB;
use PDO;

final class DbRepository
{
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

        $st = DB::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * selectOne('users', ['id'=>5])
     */
    public function selectOne(string $table, array $conditions): ?array
    {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$table}{$whereSql} LIMIT 1";
        $st  = DB::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * create('posts', ['title'=>'..', 'body'=>'..'])
     * @return int Insert-ID
     */
    public function create(string $table, array $data): int
    {
        $cols  = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);
        $sql   = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $place) . ")";
        $st    = DB::pdo()->prepare($sql);
        $st->execute($data);
        return (int) DB::pdo()->lastInsertId();
    }

    /**
     * update('posts', 5, ['title'=>'..'])
     * @return int betroffene Zeilen
     */
    public function update(string $table, int $id, array $data): int
    {
        $sets = [];
        foreach ($data as $k => $v) $sets[] = "{$k} = :{$k}";
        $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE id = :__id";
        $data['__id'] = $id;

        $st = DB::pdo()->prepare($sql);
        $st->execute($data);
        return $st->rowCount();
    }

    /**
     * delete('posts', 5)
     */
    public function delete(string $table, int $id): int
    {
        $st = DB::pdo()->prepare("DELETE FROM {$table} WHERE id = ?");
        $st->execute([$id]);
        return $st->rowCount();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Spezielle Helfer für Auth (werden von AuthController genutzt)
    // ─────────────────────────────────────────────────────────────────────

    public function findUserByUsernameOrEmail(string $username, string $email): ?array
    {
        $sql = "SELECT id, username, email FROM users
                WHERE username = :u OR email = :e
                LIMIT 1";
        $st = DB::pdo()->prepare($sql);
        $st->execute([':u' => $username, ':e' => $email]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createUser(string $username, string $email, string $passwordHash): int
    {
        $pdo = \App\OOP\Core\DB::pdo();

        // vorhandene Spalten der Tabelle users ermitteln
        $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN, 0);
        $hasCreated = in_array('created_at', $cols, true);
        $hasUpdated = in_array('updated_at', $cols, true);
        $hasAdmin   = in_array('admin',      $cols, true);

        // Basisdaten
        $data = [
            'username' => $username,
            'email'    => $email,
            'password' => $passwordHash,
        ];
        if ($hasAdmin)   { $data['admin'] = 0; }

        // Spalten-/Values-Listen dynamisch bauen
        $columns = array_keys($data);
        $place   = array_map(fn($c) => ':' . $c, $columns);

        // optionale Zeitstempel anhängen
        if ($hasCreated) { $columns[] = 'created_at'; $place[] = 'NOW()'; }
        if ($hasUpdated) { $columns[] = 'updated_at'; $place[] = 'NOW()'; }

        $sql = 'INSERT INTO users (' . implode(',', $columns) . ') VALUES (' . implode(',', $place) . ')';
        $st  = $pdo->prepare($sql);

        // Nur benannte Parameter binden (NOW() hat keinen Parameter)
        $params = array_intersect_key($data, array_flip(array_filter($columns, fn($c) => $c !== 'created_at' && $c !== 'updated_at')));
        $st->execute($params);

        return (int)$pdo->lastInsertId();
    }


   public function findUserByIdentifier(string $identifier): ?array
    {
        $sql = "SELECT * FROM users
                WHERE username = :u OR email = :e
                LIMIT 1";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':u' => $identifier, ':e' => $identifier]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }


    // ─────────────────────────────────────────────────────────────────────
    // intern
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Baut WHERE aus ['col'=>val, 'col2'=>val2] → " WHERE col=:col AND col2=:col2"
     * und liefert auch das Parameter-Array.
     *
     * @return array{0:string,1:array<string,mixed>}
     */
    private function buildWhere(array $conditions): array
    {
        if ($conditions === []) return ['', []];

        $parts  = [];
        $params = [];
        foreach ($conditions as $k => $v) {
            $param        = ':' . $k;
            $parts[]      = "{$k} = {$param}";
            $params[$param] = $v;
        }
        return [' WHERE ' . implode(' AND ', $parts), $params];
    }
    // Nur "approved" Posts für Frontend
    public function getPublishedPosts(): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.status = 'approved' -- statt p.published=1
                ORDER BY p.created_at DESC";
        $st = \App\OOP\Core\DB::pdo()->query($sql);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function submitPost(int $postId, int $userId): int
    {
        $sql = "UPDATE posts
                SET status = 'submitted', published = 0, reviewed_at = NULL, reviewer_id = NULL, review_note = NULL
                WHERE id = :id AND user_id = :uid";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':id' => $postId, ':uid' => $userId]);
        return $st->rowCount();
    }

    public function approvePost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $sql = "UPDATE posts
                SET status = 'approved', published = 1, reviewer_id = :rid, reviewed_at = NOW(), review_note = :note
                WHERE id = :id";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':rid' => $reviewerId, ':note' => $note, ':id' => $postId]);
        return $st->rowCount();
    }

    public function rejectPost(int $postId, int $reviewerId, ?string $note = null): int
    {
        $sql = "UPDATE posts
                SET status = 'rejected', published = 0, reviewer_id = :rid, reviewed_at = NOW(), review_note = :note
                WHERE id = :id";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
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
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':s' => $status]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Veröffentliche Posts zu einem Topic (für ?t_id=...)
    public function getPostsByTopicId(int $topicId): array
    {
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.published = 1
                AND p.topic_id = :tid
                ORDER BY p.created_at DESC";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':tid' => $topicId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Volltextsuche (einfach) über title/body (LIKE), nur veröffentlichte
    public function searchPosts(string $term): array
    {
        $like = '%' . $term . '%';
        $sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.published = 1
                AND (p.title LIKE :q OR p.body LIKE :q)
                ORDER BY p.created_at DESC";
        $st = \App\OOP\Core\DB::pdo()->prepare($sql);
        $st->execute([':q' => $like]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

}
