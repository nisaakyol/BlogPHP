<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class AuthController
{
    public function __construct(private DbRepository $db) {}


    // REGISTRIERUNG (mit reCAPTCHA v2)
    public function handleRegister(array $post): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        // ───────────────────────────────── Honeypot (Bot-Falle)
        if (!empty($post['honeypot'] ?? '')) {
            $_SESSION['form_errors'] = ['Ungültige Eingabe.'];
            $_SESSION['form_old']    = ['username' => '', 'email' => ''];
            header('Location: ' . BASE_URL . '/register.php');
            exit;
        }

        // ───────────────────────────────── Form-Daten
        $username     = trim((string)($post['username'] ?? ''));
        $email        = trim((string)($post['email'] ?? ''));
        $password     = (string)($post['password'] ?? '');
        $passwordConf = (string)($post['passwordConf'] ?? '');

        $errors = [];

        // ───────────────────────────────── Feld-Validierung
        if ($username === '' || mb_strlen($username) < 3) {
            $errors[] = 'Username muss mind. 3 Zeichen haben.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Gültige E-Mail angeben.';
        }
        if ($password === '' || strlen($password) < 6) {
            $errors[] = 'Passwort muss mind. 6 Zeichen haben.';
        }
        if ($password !== $passwordConf) {
            $errors[] = 'Passwörter stimmen nicht überein.';
        }

        // ───────────────────────────────── reCAPTCHA v2 (Checkbox) prüfen
        // Erwartet im Formular: <div class="g-recaptcha" data-sitekey="..."></div>
        // und POST-Feld: g-recaptcha-response
        $rcSecret = getenv('RECAPTCHA_V2_SECRET') ?: '';
        if ($rcSecret !== '') {
            $rcResp = trim((string)($post['g-recaptcha-response'] ?? ''));
            if ($rcResp === '') {
                $errors[] = 'Bitte bestätige, dass du kein Roboter bist.';
            } else {
                $payload = http_build_query([
                    'secret'   => $rcSecret,
                    'response' => $rcResp,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
                $ctx = stream_context_create([
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($payload),
                        'content' => $payload,
                        'timeout' => 5,
                    ],
                ]);
                $raw  = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
                $json = $raw ? json_decode($raw, true) : null;

                if (!$json || empty($json['success'])) {
                    $errors[] = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
                }
            }
        }
        // Hinweis: Wenn $rcSecret leer ist (DEV), wird der Check bewusst übersprungen.

        // ───────────────────────────────── Duplikate prüfen (nur wenn bisher keine Errors)
        if (!$errors) {
            $dupe = $this->db->findUserByUsernameOrEmail($username, $email);
            if ($dupe) {
                if (isset($dupe['username']) && strcasecmp($dupe['username'], $username) === 0) {
                    $errors[] = 'Username ist bereits vergeben.';
                }
                if (isset($dupe['email']) && strcasecmp($dupe['email'], $email) === 0) {
                    $errors[] = 'Diese E-Mail ist bereits registriert.';
                }
            }
        }

        // ───────────────────────────────── Fehler zurück an Formular
        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $username, 'email' => $email];
            header('Location: ' . BASE_URL . '/register.php');
            exit;
        }

        // ───────────────────────────────── User anlegen
        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->db->createUser($username, $email, $hash);

        // ───────────────────────────────── Auto-Login + Redirect
        $_SESSION['id']       = (int)$userId;
        $_SESSION['username'] = $username;
        $_SESSION['admin']    = 0;
        $_SESSION['message']  = 'Willkommen, Registrierung erfolgreich!';
        $_SESSION['type']     = 'success';

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    // LOGIN (mit reCAPTCHA v2)
    public function handleLogin(array $post): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        $identifier = trim((string)($post['username'] ?? '')); // Username 
        $password   = (string)($post['password'] ?? '');
        $errors     = [];

        // Eingaben prüfen
        if ($identifier === '') $errors[] = 'Bitte Username eingeben.';
        if ($password === '')   $errors[] = 'Bitte Passwort eingeben.';

        // reCAPTCHA v2 prüfen (Checkbox / Bilder)
        $rcSecret = getenv('RECAPTCHA_V2_SECRET') ?: '';
        $rcResp   = trim((string)($post['g-recaptcha-response'] ?? ''));
        if ($rcSecret !== '') {
            if ($rcResp === '') {
                $errors[] = 'Bitte bestätige, dass du kein Roboter bist.';
            } else {
                $postData = http_build_query([
                    'secret'   => $rcSecret,
                    'response' => $rcResp,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
                $ctx = stream_context_create([
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($postData),
                        'content' => $postData,
                        'timeout' => 5,
                    ],
                ]);
                $res  = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
                $json = $res ? json_decode($res, true) : null;
                if (!$json || empty($json['success'])) {
                    $errors[] = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
                }
            }
        }
        // Wenn kein Secret → DEV-Modus → Check wird übersprungen

        // Benutzer prüfen
        if (!$errors) {
            $user = $this->db->findUserByIdentifier($identifier);
            if (!$user || !password_verify($password, (string)$user['password'])) {
                $errors[] = 'Zugangsdaten ungültig.';
            }
        }

        // Bei Fehlern zurück zum Login
        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $identifier];
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // Erfolg → Session
        $_SESSION['id']       = (int)$user['id'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['admin']    = (int)($user['admin'] ?? 0);
        $_SESSION['message']  = 'Erfolgreich eingeloggt.';
        $_SESSION['type']     = 'success';

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    // ───────────────────────────────────────────────
    // LOGOUT
    // ───────────────────────────────────────────────
    public function handleLogout(): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? false);
        }
        session_destroy();

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
