<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class AuthController
{
    public function __construct(private DbRepository $db) {}

    public function handleLogin(array $data): void
    {
        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $errors   = [];

        if ($username === '') { $errors[] = 'Bitte Username eingeben.'; }
        if ($password === '') { $errors[] = 'Bitte Passwort eingeben.'; }

        if (!$errors) {
            $user = $this->db->selectOne('users', ['username' => $username]);
            if (!$user || !password_verify($password, (string)$user['password'])) {
                $errors[] = 'Benutzername oder Passwort ist falsch.';
            }
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $username];
            $this->redirect('login.php');
        }

        // ---- SESSION SICHER SETZEN ----
        $_SESSION['id']       = (int)$user['id'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['admin']    = (int)($user['admin'] ?? 0);
        $_SESSION['message']  = 'Du bist eingeloggt';
        $_SESSION['type']     = 'success';

        // WICHTIG: neue Session-ID & sicher wegschreiben
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
            @session_write_close(); // zwingt PHP, die Session auf die Platte zu schreiben
        }

        $target = !empty($_SESSION['admin']) ? 'admin/dashboard.php' : 'index.php';
        $this->redirect($target);
    }

    public function handleRegister(array $data): void
    {
        // (unverändert wie zuvor – hier gekürzt)
    }

    private function redirect(string $target): void
    {
        if (!headers_sent($f, $l)) {
            header("Location: {$target}", true, 302);
            exit;
        }
        echo "<!doctype html><meta charset='utf-8'>
              <p>Weiterleitung… <a href='".htmlspecialchars($target, ENT_QUOTES)."'>Weiter</a></p>
              <script>location.replace('".addslashes($target)."');</script>";
        exit;
    }
}
