<?php
declare(strict_types=1);

namespace App\OOP\Core;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;

        // ENV/Konstanten lesen (haben Vorrang)
        $host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : null);
        $port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : null);
        $name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : null);
        $user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : null);
        $pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : null);

        // Fallback: Docker-Defaults (Service-Name "db")
        $host = $host ?: 'db';
        $port = $port ?: '3306';
        $name = $name ?: 'blog';
        $user = $user ?: 'bloguser';
        $pass = $pass ?? 'blogpass';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);
            return self::$pdo;
        } catch (PDOException $e) {
            die('DB-Fehler: ' . $e->getMessage() . ' | DSN=' . $dsn . ' | USER=' . $user);
        }
    }
}
