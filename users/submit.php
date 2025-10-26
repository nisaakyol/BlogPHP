<?php
declare(strict_types=1);
require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';

use App\OOP\Controllers\PostWriteController;
use App\OOP\Repositories\DbRepository;

usersOnly();
$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) { header('Location: ' . BASE_URL . '/users/dashboard.php?tab=posts'); exit; }

(new PostWriteController(new DbRepository()))->submit($postId);
