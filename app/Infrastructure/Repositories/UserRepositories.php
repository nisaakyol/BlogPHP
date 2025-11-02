<?php
declare(strict_types=1);

// Zweck: Verwaltet alle Datenbankoperationen für Benutzer (CRUD) über PDO oder Legacy-Helper.

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Core\DB;
use PDO;

class UserRepository
{
    // Tabellenname für Benutzer
    private string $table = 'users';

    // Alle Benutzer laden
    // Rückgabe: Array aller Benutzer als assoziative Arrays
    public function all(): array
    {
        if (function_exists('selectAll')) {
            return selectAll($this->table);
        }

        $st = DB::pdo()->query("SELECT * FROM {$this->table}");
        return $st->fetchAll();
    }

    // Benutzer per ID finden
    // Gibt Benutzerarray oder null zurück
    public function findById(int $id): ?array
    {
        if (function_exists('selectOne')) {
            return selectOne($this->table, ['id' => $id]) ?: null;
        }

        $st = DB::pdo()->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    // Benutzer per Username finden
    // Gibt Benutzerarray oder null zurück
    public function findOneByUsername(string $username): ?array
    {
        if (function_exists('selectOne')) {
            return selectOne($this->table, ['username' => $username]) ?: null;
        }

        $st = DB::pdo()->prepare("SELECT * FROM {$this->table} WHERE username = ? LIMIT 1");
        $st->execute([$username]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    // Benutzer anlegen
    // Gibt die ID des neuen Benutzers zurück
    public function create(array $data): int
    {
        if (function_exists('create')) {
            return (int) create($this->table, $data);
        }

        $cols  = array_keys($data);
        $ph    = array_map(static fn ($c) => ":{$c}", $cols);
        $sql   = "INSERT INTO {$this->table} (" . implode(',', $cols) . ') VALUES (' . implode(',', $ph) . ')';
        $st    = DB::pdo()->prepare($sql);
        $st->execute($data);

        return (int) DB::pdo()->lastInsertId();
    }

    // Benutzer aktualisieren
    // Gibt die Anzahl der betroffenen Zeilen zurück
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

    // Benutzer löschen
    // Gibt die Anzahl der gelöschten Zeilen zurück
    public function delete(int $id): int
    {
        if (function_exists('delete')) {
            return (int) delete($this->table, $id);
        }

        $st = DB::pdo()->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $st->execute([$id]);

        return $st->rowCount();
    }
}
