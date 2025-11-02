<?php
declare(strict_types=1);

// Zweck: Repository-Klasse für CRUD-Operationen auf der Tabelle "topics".

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Core\DB;
use PDO;

class TopicRepository
{
    // Tabellenname zentral gehalten, um Duplikate zu vermeiden und ggf. leicht änderbar
    private string $table = 'topics';

    public function all(): array
    {
        // Legacy-Fallback: nutzt globale Helper, falls vorhanden
        if (function_exists('selectAll')) {
            return selectAll($this->table);
        }

        // Neuer Weg: einfache SELECT-Abfrage über PDO
        $st = DB::pdo()->query("SELECT * FROM {$this->table}");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ein Topic anhand der ID finden
    // Gibt ein assoziatives Array oder null zurück
    public function findById(int $id): ?array
    {
        // Legacy-Fallback: globale Helper-Funktion
        if (function_exists('selectOne')) {
            return selectOne($this->table, ['id' => $id]) ?: null;
        }

        // Neuer Weg: vorbereitete Anweisung (Schutz vor SQL-Injection)
        $st = DB::pdo()->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        // Null zurückgeben, falls kein Datensatz gefunden wurde
        return $r ?: null;
    }

    // Neues Topic anlegen
    public function create(array $data): int
    {
        // Legacy-Fallback
        if (function_exists('create')) {
            return (int) create($this->table, $data);
        }

        // Spaltennamen und Platzhalter aus dem $data-Array ableiten
        $cols = array_keys($data);
        $ph   = array_map(static fn ($c) => ":{$c}", $cols);

        // INSERT-Statement dynamisch aufbauen (nur benannte Platzhalter)
        $sql  = "INSERT INTO {$this->table} (" . implode(',', $cols) . ') VALUES (' . implode(',', $ph) . ')';

        // Vorbereiten und ausführen
        $st = DB::pdo()->prepare($sql);
        $st->execute($data);

        // Letzte Insert-ID (z. B. AUTO_INCREMENT) zurückgeben
        return (int) DB::pdo()->lastInsertId();
    }

    // Vorhandenes Topic aktualisieren
    public function update(int $id, array $data): int
    {
        // Legacy-Fallback
        if (function_exists('update')) {
            return (int) update($this->table, $id, $data);
        }

        // SET-Klausel dynamisch erzeugen: "col = :col"
        $sets = [];
        foreach ($data as $k => $v) {
            $sets[] = "{$k} = :{$k}";
        }

        // Vollständiges UPDATE-Statement zusammenbauen
        $sql        = 'UPDATE ' . $this->table . ' SET ' . implode(',', $sets) . ' WHERE id = :id';
        $data['id'] = $id; // ID als zusätzlicher Parameter

        // Ausführen und betroffene Zeilen zählen
        $st = DB::pdo()->prepare($sql);
        $st->execute($data);

        return $st->rowCount();
    }

    // Topic löschen
    // Rückgabe: Anzahl der betroffenen Zeilen
    public function delete(int $id): int
    {
        // Legacy-Fallback
        if (function_exists('delete')) {
            return (int) delete($this->table, $id);
        }

        // Löschvorgang per vorbereiteter Anweisung
        $st = DB::pdo()->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $st->execute([$id]);

        return $st->rowCount();
    }
}
