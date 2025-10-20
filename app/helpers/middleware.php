<?php
/**
 * Datei: app/helpers/middleware.php
 * Zweck: Middleware-Helfer für Zugriffsschutz (usersOnly/adminOnly) als Drop-in,
 *        delegiert an OOP-Service App\OOP\Services\AccessService.
 *
 * Verhalten:
 * - Sucht den OOP-Bootstrap relativ zu ROOT_PATH bzw. diesem Verzeichnis.
 * - Stellt usersOnly()/adminOnly() bereit, falls nicht bereits definiert.
 * - Nutzt AccessService::requireUser() / ::requireAdmin().
 */

// Versuche, das Projekt-Root zu bestimmen (ROOT_PATH bevorzugt)
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');

// Mögliche Orte für den OOP-Bootstrap (monorepo/legacy Layouts)
$__candidateA = $__root . '/app/OOP/bootstrap.php';
$__candidateB = $__root . '/OOP/bootstrap.php';

// Effektiven Bootstrap-Pfad wählen
$__boot = is_file($__candidateA) ? $__candidateA : $__candidateB;

// Bootstrap laden oder sauber scheitern
if (is_file($__boot)) {
  require_once $__boot;
} else {
  // Hinweis: In Produktion besser loggen statt die Seite hart zu beenden.
  die('Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ')');
}

use App\OOP\Services\AccessService;

/**
 * Zugriff nur für eingeloggte Benutzer (User oder Admin).
 * Leitet bei Bedarf auf Login/Fehlerseite um (vom AccessService gesteuert).
 */
if (!function_exists('usersOnly')) {
  function usersOnly(): void
  {
    AccessService::requireUser();
  }
}

/**
 * Zugriff nur für Administratoren.
 * Leitet bei Bedarf auf Login/Fehlerseite um (vom AccessService gesteuert).
 */
if (!function_exists('adminOnly')) {
  function adminOnly(): void
  {
    AccessService::requireAdmin();
  }
}