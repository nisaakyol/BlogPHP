<?php
/**
 * Datei: app/OOP/bootstrap.php
 * Erstellungsdatum: 19.10.2025
 *
 * Zweck:
 * - Session sicher initialisieren (Cookie-Parameter, Fallback für save_path).
 * - Autoloader für den Namespace "App\OOP" registrieren (einfache PSR-4-Abbildung).
 * - top_db_config() bereitstellen (liest optional /config/db.php).
 * - Legacy mysqli-Verbindung ($GLOBALS['conn']) nachladen, falls nicht vorhanden.
 *
 * Hinweise:
 * - Für Produktion bei HTTPS `secure => true` setzen.
 * - ROOT_PATH muss vor diesem Bootstrap definiert sein (z. B. über path.php).
 */

 // ---------------------------------------------------------------------------
 // Session-Setup (Cookie-Parameter + Speicherort-Fallback)
 // ---------------------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',   // gesamte Site
    'secure'   => false, // bei HTTPS: true
    'httponly' => true,
    'samesite' => 'Lax', // ausreichend für normale Redirects
]);

// Falls der Container/Server keinen Session-Pfad gesetzt hat → OS-Temp nutzen
if (!ini_get('session.save_path')) {
    ini_set('session.save_path', sys_get_temp_dir());
}

// Session starten, wenn noch nicht aktiv
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ---------------------------------------------------------------------------
// Einmaliger Bootstrap-Block (Autoloader + DB-Config-Helfer)
// ---------------------------------------------------------------------------
if (!defined('TOP_BOOTSTRAP_LOADED')) {
    define('TOP_BOOTSTRAP_LOADED', true);

    // Einfacher PSR-4-Autoloader für den Namespace "App\OOP"
    spl_autoload_register(function ($class) {
        $prefix = 'App\\OOP\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file     = __DIR__ . '/' . $relative;

        if (is_file($file)) {
            require $file;
        }
    });

    // DB-Config-Helfer: liefert DSN/User/Pass (optional aus /config/db.php)
    if (!function_exists('top_db_config')) {
        function top_db_config(): array
        {
            $file = __DIR__ . '/../../config/db.php';
            if (is_file($file)) {
                /** @var array{dsn:string,user:string,pass:string}|mixed $cfg */
                $cfg = require $file;
                // Minimal absichern, falls das File nicht wie erwartet ist
                if (is_array($cfg) && isset($cfg['dsn'], $cfg['user'], $cfg['pass'])) {
                    return $cfg;
                }
            }

            // Fallback (localhost)
            return [
                'dsn'  => 'mysql:host=127.0.0.1;dbname=blog;charset=utf8mb4',
                'user' => 'root',
                'pass' => '',
            ];
        }
    }
}

// ---------------------------------------------------------------------------
// Legacy mysqli-Verbindung verfügbar machen (falls noch nicht vorhanden)
// Erwartet: ROOT_PATH ist gesetzt (z. B. via path.php)
// ---------------------------------------------------------------------------
if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof mysqli)) {
    $__connect = ROOT_PATH . '/app/database/connect.php';
    if (is_file($__connect)) {
        require_once $__connect;
    }
}
