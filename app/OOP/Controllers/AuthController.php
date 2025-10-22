<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class AuthController
{
    public function __construct(private DbRepository $db) {}

    /** Registrierung (passend zu register.php) */
    public function handleRegister(array $post): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        // Honeypot (optional)
        if (!empty($post['honeypot'] ?? '')) {
            $_SESSION['form_errors'] = ['Ungültige Eingabe.'];
            $_SESSION['form_old']    = ['username' => '', 'email' => ''];
            header('Location: ' . BASE_URL . '/register.php'); exit;
        }

        $username     = trim((string)($post['username'] ?? ''));
        $email        = trim((string)($post['email'] ?? ''));
        $password     = (string)($post['password'] ?? '');
        $passwordConf = (string)($post['passwordConf'] ?? '');

        $errors = [];
        if ($username === '' || mb_strlen($username) < 3) $errors[] = 'Username muss mind. 3 Zeichen haben.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Gültige E-Mail angeben.';
        if ($password === '' || strlen($password) < 6) $errors[] = 'Passwort muss mind. 6 Zeichen haben.';
        if ($password !== $passwordConf) $errors[] = 'Passwörter stimmen nicht überein.';

        if (!$errors) {
            $dupe = $this->db->findUserByUsernameOrEmail($username, $email);
            if ($dupe) {
                if (strcasecmp($dupe['username'], $username) === 0) $errors[] = 'Username ist bereits vergeben.';
                if (strcasecmp($dupe['email'], $email) === 0)     $errors[] = 'Diese E-Mail ist bereits registriert.';
            }
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $username, 'email' => $email];
            header('Location: ' . BASE_URL . '/register.php'); exit;
        }

        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->db->createUser($username, $email, $hash);

        // Login-Session setzen
        $_SESSION['id']       = (int)$userId;
        $_SESSION['username'] = $username;
        $_SESSION['admin']    = 0;
        $_SESSION['message']  = 'Willkommen, Registrierung erfolgreich!';
        $_SESSION['type']     = 'success';

        header('Location: ' . BASE_URL . '/index.php'); exit;
    }

    /** Login (passt zu deiner login.php: name="username" + name="password") */
    public function handleLogin(array $post): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        $identifier = trim((string)($post['username'] ?? '')); // Username ODER E-Mail erlaubt
        $password   = (string)($post['password'] ?? '');
        $errors     = [];

        if ($identifier === '') $errors[] = 'Bitte Username oder E-Mail eingeben.';
        if ($password === '')   $errors[] = 'Bitte Passwort eingeben.';

        if (!$errors) {
            $user = $this->db->findUserByIdentifier($identifier);
            if (!$user || !password_verify($password, (string)$user['password'])) {
                $errors[] = 'Zugangsdaten ungültig.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $identifier];
            header('Location: ' . BASE_URL . '/login.php'); exit;
        }

        // Erfolg → Session
        $_SESSION['id']       = (int)$user['id'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['admin']    = (int)$user['admin']; // 0/1
        $_SESSION['message']  = 'Erfolgreich eingeloggt.';
        $_SESSION['type']     = 'success';

        // Admins ins Admin-Dashboard, normale User ins User-Dashboard
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    /** Optionaler Logout */
    public function handleLogout(): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? false);
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php'); exit;
    }
}
