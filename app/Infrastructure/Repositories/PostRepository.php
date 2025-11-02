<?php
declare(strict_types=1);

// Zweck: Repository für Posts (CRUD-Operationen, einfache Abfragen, optionaler Fallback auf Legacy-Funktionen)

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Core\DB;
use PDO;

class PostRepository
{
    private string $table = 'posts';

    // Liefert alle Posts absteigend nach created_at
    // Rückgabe: array<int, array<string,mixed>>
    public function allOrdered(): array
    {
        if (function_exists('selectAll')) {
            return selectAll($this->table, [], 'created_at DESC');
        }

        $stmt = DB::pdo()->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Liefert alle Topics alphabetisch
    // Rückgabe: array<int, array<string,mixed>>
    public function topics(): array
    {
        if (function_exists('selectAll')) {
            return selectAll('topics');
        }

        $stmt = DB::pdo()->query('SELECT * FROM topics ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Einen Post per ID laden
    public function findById(int $id): ?array
    {
        if (function_exists('selectOne')) {
            return selectOne($this->table, ['id' => $id]) ?: null;
        }

        $st = DB::pdo()->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // Posts eines Autors (User) laden
    // Rückgabe: array<int, array<string,mixed>>
    public function listByAuthor(int $userId): array
    {
        $st = DB::pdo()->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $st->execute([':uid' => $userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Post anlegen
    // Rückgabe: Insert-ID
    public function create(array $data): int
    {
        if (function_exists('create')) {
            return (int) create($this->table, $data);
        }

        $cols  = array_keys($data);
        $place = array_map(static fn($c) => ":{$c}", $cols);
        $sql   = "INSERT INTO {$this->table} (" . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';

        $st = DB::pdo()->prepare($sql);
        $st->execute($data);

        return (int) DB::pdo()->lastInsertId();
    }

    // Post aktualisieren
    // Rückgabe: Anzahl betroffener Zeilen
    public function update(int $id, array $data): int
    {
        if (function_exists('update')) {
            return (int) update($this->table, $id, $data);
        }

        $sets = [];
        foreach ($data as $k => $v) {
            $sets[] = "{$k} = :{$k}";
        }

        $sql        = 'UPDATE ' . $this->table . ' SET ' . implode(',', $sets) . ' WHERE id = :id';
        $data['id'] = $id;

        $st = DB::pdo()->prepare($sql);
        $st->execute($data);

        return $st->rowCount();
    }

    // Post löschen
    // Rückgabe: Anzahl betroffener Zeilen
    public function delete(int $id): int
    {
        if (function_exists('delete')) {
            return (int) delete($this->table, $id);
        }

        $st = DB::pdo()->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $st->execute([':id' => $id]);

        return $st->rowCount();
    }

    // Publish-Flag setzen
    // Rückgabe: Anzahl betroffener Zeilen
    public function setPublished(int $id, int $flag): int
    {
        return $this->update($id, ['published' => $flag]);
    }
}
