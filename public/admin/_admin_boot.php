<?php
declare(strict_types=1);

// Pfade/URLs (ROOT_PATH, BASE_URL)
require_once __DIR__ . '/../path.php';

// App-Bootstrap (Autoload, DB, etc.)
require_once ROOT_PATH . '/app/bootstrap.php';

// Middleware/Guards
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
