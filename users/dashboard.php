<?php
/**
 * Datei: users/dashboard.php
 * Zweck: Gemeinsamer Einstieg ins Dashboard für ALLE eingeloggten Nutzer.
 *
 * Verhalten:
 * - Prüft nur Login (usersOnly) – KEIN Admin-Zwang.
 * - Leitet anschließend ins Dashboard unter /admin/index.php weiter.
 *   (Stelle sicher, dass dort ebenfalls usersOnly() steht.)
 */

declare(strict_types=1);

require_once __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';   // bringt usersOnly()
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

// Nur Login nötig – keine Admin-Pflicht
usersOnly();

// Ziel definieren (gemeinsames Dashboard)
$target = '/admin/dashboard.php'; // <- diese Seite muss usersOnly() nutzen!

header('Location: ' . BASE_URL . $target);
exit;
