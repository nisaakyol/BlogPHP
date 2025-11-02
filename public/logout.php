<?php
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php'; 
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/AuthController.php';

use App\Http\Controllers\AuthController; // Logout ausführen
use App\Infrastructure\Repositories\DbRepository;  // Repo für DB-Zugriff

try {
    (new AuthController(new DbRepository()))->logout(); // Controller-Logout
} catch (\Throwable $e) {
    // Fallback-Logout
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    session_unset();
    session_destroy();
    $_SESSION = [];
    header('Location: ' . BASE_URL . '/public/index.php');
    exit;
}
