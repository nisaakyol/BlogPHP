<?php
declare(strict_types=1);

use App\OOP\Services\AccessService;
use App\OOP\Services\AuthService;
use App\OOP\Repositories\PostRepository;
use App\OOP\Repositories\CommentRepository;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Legacy-Helfer (Kompatibilität):
 * - usersOnly(): eingeloggter User genügt
 * - adminOnly(): nur Admin
 * - isLoggedIn(), isAdmin(): kleine Helfer für Views
 */

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

/**
 * Optional (für Views/Controller, wenn du Checks brauchst)
 */
function accessService(): AccessService
{
    static $svc = null;
    if ($svc === null) {
        $svc = new AccessService(new AuthService(), new PostRepository(), new CommentRepository());
    }
    return $svc;
}
