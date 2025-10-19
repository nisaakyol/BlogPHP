<?php
/* Veränderungsdatum: 08.10.2024
   Verbindung mit der Datenbank mit gegebenen Parametern (Legacy + OOP-ready)
*/

// --- Legacy-Parameter (unverändert) ---
$host    = 'db';
$user    = 'root';
$pass    = '';
$db_name = 'blog';

// --- Legacy-Verbindung (unverändert) - mysqli ---
$conn = mysqli_connect($host, $user, $pass, $db_name);

// Passwort auf der DB nicht gesetzt. Daher wird keine Fehlermeldung ausgeworfen. Passwort muss noch in der DB gesetzt werden
if ($conn->connect_error) {
    die('Database connection error: ' . $conn->connect_error);
}

// --- OOP-ready: top_db_config() für PDO-Schicht bereitstellen ---
// (Nur definieren, wenn nicht bereits existiert – gleiche Credentials wie oben)
if (!function_exists('top_db_config')) {
    function top_db_config(): array {
        // DSN aus den Legacy-Variablen bauen
        $h = $GLOBALS['host']    ?? '127.0.0.1';
        $db= $GLOBALS['db_name'] ?? '';
        $u = $GLOBALS['user']    ?? 'root';
        $p = $GLOBALS['pass']    ?? '';
        return [
            'dsn'  => 'mysql:host=' . $h . ';dbname=' . $db . ';charset=utf8mb4',
            'user' => $u,
            'pass' => $p,
        ];
    }
}

/*  Testung der Verbindung

    else {
        echo "DB connection sucessful";
    }
*/
