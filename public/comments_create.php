<?php
declare(strict_types=1);

require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\CommentController;
use App\OOP\Repositories\CommentRepository;

if (session_status() === PHP_SESSION_NONE) session_start();

$ctrl = new CommentController(new CommentRepository());
$ctrl->store($_POST);
