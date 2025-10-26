<?php
// Lädt den OOP-Autoloader genau einmal – IMMER mit /app/OOP/bootstrap.php
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..'); // -> Projekt-Root
$__boot = $__root . '/app/OOP/bootstrap.php';
if (!defined('TOP_BOOTSTRAP_LOADED')) {
    if (is_file($__boot)) {
        require_once $__boot;
    } else {
        // Klarer Fehler mit vollem Pfad
        die('Autoload-Fehler: ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ' fehlt');
    }
}
$vendor = ROOT_PATH . '/vendor/autoload.php';
if (is_file($vendor)) {
    require_once $vendor;
}
