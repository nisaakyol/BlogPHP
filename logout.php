<?php
require 'path.php'; // Lädt Projektpfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap_once.php'; // Bootstrap/Autoloader der OOP-Schicht

use App\OOP\Controllers\AuthController; // Controller für Authentifizierung (Login/Logout)
use App\OOP\Repositories\DbRepository;  // DB-Zugriffsschicht (per DI in den Controller)

/**
 * Versucht, den OOP-Logout-Flow auszuführen.
 * - Erwartet, dass AuthController::logout() Session säubert und Redirect übernimmt.
 * - Bei Fehlern (z. B. Methode (noch) nicht vorhanden) greift der Fallback im catch.
 */
try {
    (new AuthController(new DbRepository()))->logout(); // DI: Repository wird dem Controller übergeben; direkte Einmal-Instanzierung
} catch (\Throwable $e) {
    // Fallback, falls die Methode im Controller (noch) nicht existiert
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(); // Session sicherstellen
    session_unset();   // Alle Session-Variablen leeren
    session_destroy(); // Session beenden (Server-seitig)
    $_SESSION = [];    // Referenzseitig leeren (Sicherheits-/Klarheitsmaßnahme)
    header('Location: ' . BASE_URL . '/index.php'); // Zur Startseite zurück
    exit; // Skript beenden, damit nach dem Redirect nichts mehr ausgeführt wird
}
