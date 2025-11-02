<?php
declare(strict_types=1);

// Zweck: Zentraler App-Bootstrap (Session, Autoloader, optionale PDO-Init). Erwartet zuvor geladenes path.php (ROOT_PATH/BASE_URL).

// Fallback, falls ROOT_PATH noch nicht gesetzt ist
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(str_replace('\\', '/', dirname(__DIR__)), '/'));
}

// 1) Session starten (nur wenn nicht aktiv und bevor Header gesendet wurden)
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false,   // bei HTTPS auf true setzen
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        if (!ini_get('session.save_path')) {
            ini_set('session.save_path', sys_get_temp_dir());
        }
    }
    @session_start();
}

// 2) Composer-Autoload laden (falls vorhanden)
$composer = ROOT_PATH . '/vendor/autoload.php';
if (is_file($composer)) {
    require_once $composer;
}

// 3) Fallback-PSR-4-Autoloader für "App\" → ROOT_PATH/app/ (wenn kein Composer-Autoloader aktiv)
if (!class_exists(\Composer\Autoload\ClassLoader::class, false)) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    });
}

// 3b) Alternativer Fallback-Autoloader (präpend, wirft bei Fehlern)
if (!class_exists(\Composer\Autoload\ClassLoader::class, false)) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
        $relative = substr($class, strlen($prefix));
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }, true, true);
}

// 4) PDO initialisieren (Konstanten kommen aus path.php)
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );
    $pdo = new PDO($dsn, (string) DB_USER, (string) DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $GLOBALS['pdo'] = $pdo; // global verfügbar machen

    // Optional in zentralem DB-Wrapper registrieren
    if (
        class_exists(\App\Infrastructure\Core\DB::class)
        && method_exists(\App\Infrastructure\Core\DB::class, 'initWithPdo')
    ) {
        \App\Infrastructure\Core\DB::initWithPdo($pdo);
    }
} catch (\Throwable $e) {
    // Keine Ausgabe nach außen; nur Log für Dev
    error_log('DB init failed: ' . $e->getMessage());
}

// 5) Kleine Helper-Funktionen
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        return rtrim(ROOT_PATH . '/' . ltrim($path, '/'), '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('require_first')) {
    // Lädt den ersten existierenden Kandidaten relativ zu ROOT_PATH, sonst 500 + Log
    function require_first(string ...$candidates): void {
        foreach ($candidates as $rel) {
            $abs = rtrim(ROOT_PATH, '/') . '/' . ltrim($rel, '/');
            if (is_file($abs)) {
                require_once $abs;
                return;
            }
        }
        http_response_code(500);
        error_log('require_first failed. Tried: ' . implode(' | ', $candidates));
        exit('Datei nicht gefunden. Versucht: ' . implode(' | ', $candidates));
    }
}
