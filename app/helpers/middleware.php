<?php
// TOP Drop-in: stellt usersOnly()/adminOnly() via OOP bereit – robuster Bootstrap
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');
$__boot = is_file($__root . '/app/OOP/bootstrap.php')
        ? $__root . '/app/OOP/bootstrap.php'
        : $__root . '/OOP/bootstrap.php'; // Fallback, falls ROOT_PATH auf /app zeigt
if (is_file($__boot)) {
    require_once $__boot;
} else {
    die('Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter '.$__boot.')');
}

use App\OOP\Services\AccessService;

if (!function_exists('usersOnly')) {
    function usersOnly(): void { AccessService::requireUser(); }
}
if (!function_exists('adminOnly')) {
    function adminOnly(): void { AccessService::requireAdmin(); }
}
