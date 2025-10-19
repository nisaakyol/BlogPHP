<?php
// Einheitlicher Bootstrap für alle Admin-Seiten
require_once __DIR__ . '/../path.php';                         // ROOT_PATH, BASE_URL
require_once ROOT_PATH . '/app/database/db.php';              // Legacy-DB-Funktionen (TOP-Shim)
require_once ROOT_PATH . '/app/helpers/middleware.php';       // usersOnly(), adminOnly()

// OOP-Bootstrap (Autoloader)
$__boot = ROOT_PATH . '/app/OOP/bootstrap.php';
if (is_file($__boot)) require_once $__boot;

// Standard-Header/Sidebar einbinden nutzt BASE_URL
