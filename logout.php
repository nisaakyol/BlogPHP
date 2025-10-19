<?php
require 'path.php';
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\AuthController;
use App\OOP\Repositories\DbRepository;

try {
    (new AuthController(new DbRepository()))->logout();
} catch (\Throwable $e) {
    // Fallback, falls die Methode im Controller (noch) nicht existiert
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    session_unset();
    session_destroy();
    $_SESSION = [];
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
