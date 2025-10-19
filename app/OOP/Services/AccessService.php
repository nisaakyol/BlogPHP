<?php
namespace App\OOP\Services;

class AccessService {
    /** analog usersOnly() */
    public static function requireUser(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . '/login.php');
            exit();
        }
    }

    /** analog adminOnly() */
    public static function requireAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . '/login.php');
            exit();
        }
        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Nicht erlaubt';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . '/index.php');
            exit();
        }
    }
}
