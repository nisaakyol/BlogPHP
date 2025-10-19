<?php
namespace App\OOP\Core;

use PDO, PDOException;

class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) {
            $cfg = function_exists('top_db_config') ? top_db_config() : [
                'dsn'  => 'mysql:host=127.0.0.1;dbname=blog;charset=utf8mb4',
                'user' => 'root',
                'pass' => '',
            ];
            try {
                self::$pdo = new PDO($cfg['dsn'], $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) { die('DB-Fehler: '.$e->getMessage()); }
        }
        return self::$pdo;
    }
}
