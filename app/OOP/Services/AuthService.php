<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * AuthService
 *
 * - Setzt Session-Variablen
 * - Flash-Message
 * - Redirect je nach Rolle (Admin → /admin/dashboard.php, sonst /index.php)
 */
class AuthService
{
    /**
     * Loggt einen Nutzer ein und leitet entsprechend weiter.
     *
     * @param array $user Erwartet Keys: id, username, admin
     * @return void
     */
    public static function loginUser(array $user): void
    {
        $_SESSION['id']       = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['admin']    = $user['admin'];
        $_SESSION['message']  = 'Du bist Eingeloggt';
        $_SESSION['type']     = 'success';

        if (!empty($_SESSION['admin'])) {
            header('location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('location: ' . BASE_URL . '/index.php');
        }
        exit();
    }
}
