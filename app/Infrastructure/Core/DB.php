<?php
declare(strict_types=1);

// Zweck: Stellt eine einmalig initialisierte (Singleton) PDO-Verbindung zur MySQL-Datenbank bereit.

namespace App\Infrastructure\Core;

use PDO;
use PDOException;

class DB
{
    // Caches die PDO-Instanz für die gesamte Laufzeit
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        // Bereits initialisierte Verbindung zurückgeben
        if (self::$pdo) return self::$pdo;

        // ENV/Konstanten lesen (haben Vorrang vor Defaults)
        $host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : null);
        $port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : null);
        $name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : null);
        $user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : null);
        $pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : null);

        // Fallback-Defaults für Docker-Compose-Setup
        $host = $host ?: 'db';
        $port = $port ?: '3306';
        $name = $name ?: 'blog';
        $user = $user ?: 'bloguser';
        $pass = $pass ?? 'blogpass';

        // DSN mit UTF-8 (utf8mb4) konfigurieren
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            // PDO initialisieren mit sinnvollen Defaults
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceptions statt Silent-Fails
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Assoziative Arrays als Standard
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Native Prepared Statements
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",   // Zeichensatz auch serverseitig
            ]);

            // Erfolgreiche Verbindung zurückgeben
            return self::$pdo;
        } catch (PDOException $e) {
            // Harte Fehlermeldung inkl. DSN/USER (für Debug lokal ok; in Prod vermeiden)
            die('DB-Fehler: ' . $e->getMessage() . ' | DSN=' . $dsn . ' | USER=' . $user);
        }
    }
}
