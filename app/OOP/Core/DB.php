<?php
declare(strict_types=1);

namespace App\OOP\Core;

use PDO;
use PDOException;

/**
 * DB
 * PDO-Singleton mit robuster Konfig-Autoerkennung:
 * Reihenfolge: ENV → definierte Konstanten → Legacy top_db_config() → Docker-Default
 */
class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        // 1) ENV / .env (z. B. via docker-compose)
        $host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : null);
        $port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : null);
        $name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : null);
        $user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : null);
        $pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : null);

        // 2) Legacy top_db_config()
        if (!$host && function_exists('top_db_config')) {
            $cfg  = top_db_config();
            $dsn  = $cfg['dsn'] ?? null;
            $user = $cfg['user'] ?? $user;
            $pass = $cfg['pass'] ?? $pass;

            if ($dsn) {
                // DSN direkt verwenden (aber auf utf8mb4 upgraden, falls nötig)
                if (!str_contains($dsn, 'charset=')) {
                    $dsn .= ';charset=utf8mb4';
                } else {
                    $dsn = preg_replace('/charset=\w+/i', 'charset=utf8mb4', $dsn);
                }
                return self::$pdo = self::connect($dsn, (string)$user, (string)$pass);
            }
        }

        // 3) Docker-Default (Service-Name "db")
        $host = $host ?: 'db';        // WICHTIG: nicht 127.0.0.1 in Docker
        $port = $port ?: '3306';
        $name = $name ?: 'blog';
        $user = $user ?: 'root';
        $pass = $pass ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        return self::$pdo = self::connect($dsn, (string)$user, (string)$pass);
    }

    private static function connect(string $dsn, string $user, string $pass): PDO
    {
        try {
            $pdo = new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // In Produktion besser loggen
            die('DB-Fehler: ' . $e->getMessage());
        }
    }
}
