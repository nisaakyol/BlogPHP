<?php
declare(strict_types=1);

namespace App\OOP\Core;

use PDO;
use PDOException;

/**
 * DB
 *
 * Einfache PDO-Singleton-Factory.
 * - Nutzt (falls vorhanden) top_db_config() aus dem Legacy-Setup.
 * - Fallback-DSN zeigt auf mysql:127.0.0.1/blog mit utf8mb4.
 * - ERRMODE_EXCEPTION & FETCH_ASSOC sind gesetzt.
 */
class DB
{
    private static ?PDO $pdo = null;

    /**
     * Liefert eine (einmalig initialisierte) PDO-Instanz.
     */
    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            // Config aus Legacy (top_db_config) oder Fallback
            $cfg = function_exists('top_db_config')
                ? top_db_config()
                : [
                    'dsn'  => 'mysql:host=127.0.0.1;dbname=blog;charset=utf8mb4',
                    'user' => 'root',
                    'pass' => '',
                ];

            try {
                self::$pdo = new PDO(
                    $cfg['dsn'],
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false, // native prepares, wenn möglich
                        // sorgt für korrekten Zeichensatz bei älteren MySQL-Setups
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    ]
                );
            } catch (PDOException $e) {
                // In Produktion besser loggen statt die Nachricht direkt auszugeben
                die('DB-Fehler: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
