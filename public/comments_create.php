<?php
declare(strict_types=1);

require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Http/Controllers/CommentController.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/CommentRepository.php';

use App\Http\Controllers\CommentController;      // Controller
use App\Infrastructure\Repositories\CommentRepository; // Repo

if (session_status() === PHP_SESSION_NONE) session_start(); // Session sicherstellen

$ctrl = new CommentController(new CommentRepository()); // Repo in Controller
$ctrl->store($_POST); // Kommentar speichern
