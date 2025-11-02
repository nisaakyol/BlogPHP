<?php
declare(strict_types=1);

// Zweck: Zugriffsschutz (User/Admin) + kleine Auth-Helper-Funktionen

require_once ROOT_PATH . '/app/Infrastructure/Services/AccessService.php';
require_once ROOT_PATH . '/app/Infrastructure/Services/AuthService.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/PostRepository.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/CommentRepository.php';

use App\Infrastructure\Services\AccessService;
use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Repositories\PostRepository;
use App\Infrastructure\Repositories\CommentRepository;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usersOnly(): void
{
    AccessService::requireUser();
}

function adminOnly(): void
{
    AccessService::requireAdmin();
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['id']);
}

function isAdmin(): bool
{
    return !empty($_SESSION['admin']);
}

function accessService(): AccessService
{
    static $svc = null;
    if ($svc === null) {
        $svc = new AccessService(new AuthService(), new PostRepository(), new CommentRepository());
    }
    return $svc;
}
