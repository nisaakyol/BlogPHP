<?php
/**
 * Datei: admin/_admin_boot.php
 * Zweck: Einheitlicher Bootstrap für alle Admin-Seiten
 *
 * Lädt:
 * - Pfad-/Konstanten (ROOT_PATH, BASE_URL)
 * - DB-Anbindung (Legacy-Shim)
 * - Middleware (usersOnly(), adminOnly())
 * - Optionalen OOP-Bootstrap/Autoloader
 *
 * Hinweis:
 * - Falls Sessions nicht an anderer Stelle gestartet werden, sollte session_start()
 *   im globalen Bootstrap erfolgen (hier oder z. B. in db.php / path.php),
 *   damit die Middleware auf $_SESSION zugreifen kann.
 */

// Basis-Pfade/Konstanten (ROOT_PATH, BASE_URL)
require_once __DIR__ . '/../path.php';

// Legacy-DB-Funktionen (TOP-Shim) – stellt DB-Zugriff für Alt- und OOP-Teile bereit
require_once ROOT_PATH . '/app/database/db.php';

// Zugriffsschutz / Middleware-Helfer (usersOnly(), adminOnly())
require_once ROOT_PATH . '/app/helpers/middleware.php';

// OOP-Bootstrap (Autoloader) – nur laden, wenn vorhanden
$__boot = ROOT_PATH . '/app/OOP/bootstrap.php';
if (is_file($__boot)) {
  require_once $__boot;
}

// Standard-Header/Sidebar in den Admin-Views verwenden BASE_URL (kommt aus path.php)
