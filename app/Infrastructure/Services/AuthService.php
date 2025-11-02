<?php
declare(strict_types=1);

namespace App\Infrastructure\Services;

// Zweck: Session-Login und Redirect nach erfolgreicher Authentifizierung
class AuthService
{

    public static function loginUser(array $user): void
    {
        // Session sicherstellen
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            @session_start();
        }

        // Session-Variablen setzen (hart gecastet für Stabilität)
        $_SESSION['id']       = (int)($user['id'] ?? 0);
        $_SESSION['username'] = (string)($user['username'] ?? '');
        $_SESSION['admin']    = (int)($user['admin'] ?? 0);
        $_SESSION['message']  = 'Du bist eingeloggt';
        $_SESSION['type']     = 'success';

        // Ziel-URL abhängig von Admin-Flag (zeigt auf /public)
        $base = \defined('BASE_URL') ? \rtrim(\BASE_URL, '/') : '';
        if (!empty($_SESSION['admin'])) {
            header('Location: ' . $base . '/public/admin/dashboard.php');
        } else {
            header('Location: ' . $base . '/public/index.php');
        }
        exit();
    }
}
