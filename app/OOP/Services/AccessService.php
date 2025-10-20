<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * AccessService
 *
 * Stellt Zugriffsprüfungen bereit – analog zu usersOnly()/adminOnly().
 * Leitet bei fehlender Berechtigung auf login.php bzw. index.php um
 * und setzt passende Flash-Messages.
 */
class AccessService
{
    /**
     * Erfordert einen eingeloggten Benutzer.
     * Bei fehlender Session-ID → Redirect auf /login.php mit Fehlermeldung.
     */
    public static function requireUser(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/login.php');
            exit();
        }
    }

    /**
     * Erfordert einen Admin.
     * - Ohne Login → Redirect auf /login.php
     * - Ohne Admin-Flag → Redirect auf /index.php
     */
    public static function requireAdmin(): void
    {
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Bitte melde dich zuerst an';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/login.php');
            exit();
        }

        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = $_SESSION['message'] ?? 'Nicht erlaubt';
            $_SESSION['type']    = $_SESSION['type']    ?? 'error';

            $base = defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . rtrim($base, '/') . '/index.php');
            exit();
        }
    }
}
