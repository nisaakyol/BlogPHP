<?php
// Grund-Setup: Pfade laden und System initialisieren
declare(strict_types=1);


require __DIR__ . '/path.php';
// Debug-Ausgabe zur Überprüfung von Projektpfad und Basis-URL
echo "<pre>ROOT_PATH = " . ROOT_PATH . "\nBASE_URL  = " . BASE_URL . "</pre>";
// Haupt-Bootstrap laden (Konfiguration, Autoloading, Sessions)
require ROOT_PATH . '/app/bootstrap.php';

