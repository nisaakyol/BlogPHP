<?php
/*
 * Datei: app/database/db.php
 * Zweck: Aufbau der Legacy-DB-Verbindung (mysqli) + Bereitstellung einer PDO-Konfiguration (OOP-ready)
 *
 * Hinweise:
 * - Die Zugangsdaten sind hier statisch hinterlegt (Legacy). Für Produktion besser via ENV (z. B. Docker, .env).
 * - Die mysqli-Verbindung nutzt utf8mb4.
 * - Zusätzlich stellt top_db_config() DSN/User/Pass für eine PDO-Schicht bereit.
 */

/* ------------------------------------------------------------------------
 * Legacy-Parameter (unverändert)
 * ---------------------------------------------------------------------- */
$host    = 'db';
$user    = 'root';
$pass    = '';
$db_name = 'blog';

/* ------------------------------------------------------------------------
 * Legacy-Verbindung (mysqli)
 * ---------------------------------------------------------------------- */
$conn = mysqli_connect($host, $user, $pass, $db_name);

// Zeichensatz auf utf8mb4 setzen (wichtig für Emojis/Mehrbyte-Zeichen)
if ($conn instanceof mysqli) {
  mysqli_set_charset($conn, 'utf8mb4');
}

// Fehlerbehandlung: Verbindung prüfen
if ($conn instanceof mysqli && $conn->connect_error) {
  // Hinweis: In Produktion statt 'die()' besser Logging + freundliche Fehlerseite.
  die('Database connection error: ' . $conn->connect_error);
}

/* ------------------------------------------------------------------------
 * OOP-ready: PDO-Konfiguration bereitstellen
 * - Nutzt die gleichen Credentials wie oben.
 * - Nur definieren, wenn Funktion noch nicht existiert.
 * ---------------------------------------------------------------------- */
if (!function_exists('top_db_config')) {
  /**
   * Liefert Konfigurationswerte für eine PDO-Verbindung.
   *
   * @return array{dsn: string, user: string, pass: string}
   */
  function top_db_config(): array
  {
    // DSN aus den Legacy-Variablen bauen (Fallbacks, falls Globals fehlen)
    $h  = $GLOBALS['host']    ?? '127.0.0.1';
    $db = $GLOBALS['db_name'] ?? '';
    $u  = $GLOBALS['user']    ?? 'root';
    $p  = $GLOBALS['pass']    ?? '';

    return [
      'dsn'  => 'mysql:host=' . $h . ';dbname=' . $db . ';charset=utf8mb4',
      'user' => $u,
      'pass' => $p,
    ];
  }
}