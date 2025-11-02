<?php
declare(strict_types=1);
require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/PostWriteController.php';

use App\Http\Controllers\PostWriteController;
use App\Infrastructure\Repositories\DbRepository;

usersOnly();
$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) { header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts'); exit; }

(new PostWriteController(new DbRepository()))->submit($postId);
