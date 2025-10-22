<?php
/**
 * Datei: app/database/db.php
 * Zweck: OOP-only Bootstrap für die DB – KEIN Legacy, KEIN mysqli, KEINE Funktions-API.
 *
 * Aufgaben:
 *  - (Optional) Session starten, falls noch nicht aktiv.
 *  - OOP-Bootstrap laden: app/OOP/bootstrap.php (PSR-4, Services, Repositories).
 *  - PDO-Verbindung initialisieren über App\OOP\Core\DB::pdo() – Fehler transparent ausgeben.
 *
 * Hinweise:
 *  - Diese Datei exportiert KEINE Legacy-Funktionen (selectAll, selectOne, …) und KEIN $conn.
 *  - Code, der bisher Legacy-Funktionen nutzte, muss auf Repositories umgestellt werden.
 */

declare(strict_types=1);

// 0) (Optional) Session – nur falls nicht schon woanders gestartet
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// 1) Projekt-Root ermitteln
$__root = defined('ROOT_PATH')
    ? ROOT_PATH
    : realpath(__DIR__ . '/..' . '/..'); // app/database -> app -> (..) = Projekt-Root

if (!$__root) {
    die('DB-Fehler: ROOT_PATH konnte nicht ermittelt werden.');
}

// 2) OOP-Bootstrap laden (nur einmal)
$__oop_boot = $__root . '/app/OOP/bootstrap.php';
if (!defined('TOP_BOOTSTRAP_LOADED')) {
    if (is_file($__oop_boot)) {
        require_once $__oop_boot;
    } else {
        die('DB-Fehler: OOP-Bootstrap fehlt unter ' . htmlspecialchars($__oop_boot, ENT_QUOTES, 'UTF-8'));
    }
}

// 3) PDO initialisieren – bricht bei Fehlern mit klarer Meldung ab
try {
    // Verbindungsaufbau (nutzt App\OOP\Core\DB – respektiert ENV/Konstanten)
    \App\OOP\Core\DB::pdo();
} catch (\Throwable $e) {
    // Transparente Fehlermeldung inkl. Hinweis
    die('DB-Fehler beim Initialisieren der PDO-Verbindung: ' . $e->getMessage());
}

// 4) Fertig – ab hier stehen Autoloader & PDO für Repositories bereit.
//    Beispiel-Nutzung außerhalb dieser Datei:
//    $repo = new \App\OOP\Repositories\DbRepository();
//    $posts = $repo->getPublishedPosts();
