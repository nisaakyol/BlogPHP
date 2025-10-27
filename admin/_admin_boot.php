<?php
/**
 * Admin-Bootstrap: Pfade, DB, Middleware, Autoload – alles zentral.
 */
require_once __DIR__ . '/../path.php';                          // ROOT_PATH / BASE_URL
require_once ROOT_PATH . '/app/helpers/middleware.php';         // usersOnly(), adminOnly()

// OOP-Boot nur noch über zentrale Datei sicherstellen:
require_once ROOT_PATH . '/app/includes/bootstrap.php';
