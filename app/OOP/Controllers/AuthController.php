<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

/**
 * Datei: App/OOP/Controllers/AuthController.php
 *
 * Zweck:
 * - Login-/Register-Flow kapseln.
 * - Setzt Sessions sicher und leitet je nach Rolle um.
 *
 * Hinweise:
 * - Session-Start erfolgt im globalen Bootstrap.
 * - Redirects nutzen relative Ziele; BASE_URL kann bei Bedarf ergänzt werden.
 */
final class AuthController
{
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Verarbeitet das Login-Formular.
     * - Validiert Pflichtfelder
     * - Prüft Zugangsdaten
     * - Setzt Session-Attribute und regeneriert die Session-ID
     * - Leitet je nach Rolle (Admin/User) auf das passende Dashboard um
     *
     * @param array $data typischerweise $_POST
     * @return void
     */
    public function handleLogin(array $data): void
    {
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $errors   = [];

        if ($username === '') {
            $errors[] = 'Bitte Username eingeben.';
        }
        if ($password === '') {
            $errors[] = 'Bitte Passwort eingeben.';
        }

        // User vorab definieren, damit es nach der Prüfung sicher existiert
        $user = null;

        if (!$errors) {
            $user = $this->db->selectOne('users', ['username' => $username]);
            if (!$user || !password_verify($password, (string) ($user['password'] ?? ''))) {
                $errors[] = 'Benutzername oder Passwort ist falsch.';
            }
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['username' => $username];
            $this->redirect('login.php');
        }

        // ---- SESSION SICHER SETZEN ----
        $_SESSION['id']       = (int) ($user['id'] ?? 0);
        $_SESSION['username'] = (string) ($user['username'] ?? '');
        $_SESSION['admin']    = (int) ($user['admin']    ?? 0);
        $_SESSION['message']  = 'Du bist eingeloggt';
        $_SESSION['type']     = 'success';

        // Neue Session-ID ausgeben und Session persistieren
        if (session_status() === \PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
            @session_write_close(); // zwingt PHP, die Session zu speichern
        }

        $target = !empty($_SESSION['admin']) ? 'admin/dashboard.php' : 'index.php';
        $this->redirect($target);
    }

    /**
     * Verarbeitet das Registrieren (Platzhalter – Logik bleibt wie zuvor, hier nicht erneut implementiert).
     *
     * @param array $data typischerweise $_POST
     * @return void
     */
    public function handleRegister(array $data): void
    {
        // (unverändert wie zuvor – hier bewusst gekürzt / Platzhalter)
    }

    /**
     * Robuste Weiterleitung mit Fallback, falls Header bereits gesendet wurden.
     *
     * @param string $target Ziel-URL (relativ oder absolut)
     * @return void
     */
    private function redirect(string $target): void
    {
        if (!headers_sent($file, $line)) {
            header("Location: {$target}", true, 302);
            exit;
        }

        // Fallback-Weiterleitung (HTML/JS), falls bereits Output gesendet wurde
        $safeTarget = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
        echo "<!doctype html><meta charset='utf-8'>
              <p>Weiterleitung… <a href='{$safeTarget}'>Weiter</a></p>
              <script>location.replace(" . json_encode($target) . ");</script>";
        exit;
    }
}
