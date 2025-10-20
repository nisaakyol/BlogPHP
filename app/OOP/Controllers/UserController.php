<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\UserRepository;
use App\OOP\Services\AuthService;

/**
 * UserController
 *
 * Spiegelt das Legacy-Verhalten:
 * - bootInitialState(): Listet User und prefüllt Formular bei ?id=…
 * - handleRegisterOrCreateAdmin(): Registrierung oder Admin-Erstellung
 * - handleUpdateUser(): Benutzer aktualisieren
 * - handleLogin(): Login verarbeiten
 * - handleDelete(): Benutzer löschen
 *
 * Erwartete Helfer/Globals: adminOnly(), validateUser(), validateLogin(), BASE_URL
 */
class UserController
{
    private UserRepository $repo;

    // Legacy-Variablen-Zustand für die Views:
    public string $table        = 'users';
    public array  $admin_users  = [];
    public array  $errors       = [];

    public string $username     = '';
    public string $id           = '';
    public string $admin        = '';
    public string $email        = '';
    public string $password     = '';
    public string $passwordConf = '';

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    /**
     * Lädt alle User und prefüllt die Formularwerte bei ?id=…
     */
    public function bootInitialState(): void
    {
        $this->admin_users = $this->repo->all();

        // Preload bei GET id
        if (isset($_GET['id'])) {
            $user = $this->repo->findById((int) $_GET['id']);
            if ($user) {
                $this->id       = (string) $user['id'];
                $this->username = (string) $user['username'];
                $this->admin    = ((int) ($user['admin'] ?? 0) === 1) ? '1' : '0';
                $this->email    = (string) $user['email'];
            }
        }
    }

    /**
     * Honeypot wie im Legacy (bei jedem POST).
     * Erkennt Bots und beendet den Request.
     */
    public function enforceHoneypotOrDie(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!empty($_POST['honeypot'] ?? '')) {
                die('Bot-Aktivität erkannt. Der Zugriff wurde verweigert.');
            }
        }
    }

    /**
     * Registrierung oder Admin-Erstellung verarbeiten.
     * - Wenn "create-admin" gesetzt: Admin anlegen, Flash + Redirect zur Adminliste.
     * - Sonst: normaler User und Login via AuthService.
     * - Bei Fehlern: Formularwerte zurück in Properties (Legacy-kompatibel).
     */
    public function handleRegisterOrCreateAdmin(): void
    {
        if (!isset($_POST['register-btn']) && !isset($_POST['create-admin'])) {
            return;
        }

        $this->errors = validateUser($_POST);

        if (count($this->errors) === 0) {
            unset($_POST['register-btn'], $_POST['passwordConf'], $_POST['create-admin'], $_POST['honeypot']);

            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if (isset($_POST['admin'])) {
                // Admin anlegen
                $_POST['admin'] = 1;

                $this->repo->create($_POST);

                $_SESSION['message'] = 'Admin user erfolgreich erstellt';
                $_SESSION['type']    = 'success';

                header('location: ' . BASE_URL . '/admin/users/index.php');
                exit();
            }

            // Normaler User
            $_POST['admin'] = 0;

            $user_id = $this->repo->create($_POST);
            $user    = $this->repo->findById($user_id);

            AuthService::loginUser($user);
        } else {
            // Formwerte beibehalten
            $this->username     = $_POST['username']     ?? '';
            $this->admin        = isset($_POST['admin']) ? '1' : '0';
            $this->email        = $_POST['email']        ?? '';
            $this->password     = $_POST['password']     ?? '';
            $this->passwordConf = $_POST['passwordConf'] ?? '';
        }
    }

    /**
     * Benutzer aktualisieren (nur Admin).
     * - Validierung, Hashen des Passworts, Update und Redirect.
     * - Bei Fehlern: Formularwerte in Properties erhalten.
     */
    public function handleUpdateUser(): void
    {
        if (!isset($_POST['update-user'])) {
            return;
        }

        adminOnly();

        $this->errors = validateUser($_POST);

        if (count($this->errors) === 0) {
            $id = (int) $_POST['id'];

            unset($_POST['passwordConf'], $_POST['update-user'], $_POST['id']);

            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $_POST['admin']    = isset($_POST['admin']) ? 1 : 0;

            $this->repo->update($id, $_POST);

            // exakt der gleiche (inkl. Tippfehler) Text aus deinem Legacy:
            $_SESSION['message'] = 'Admin user erfoglreich erstellt';
            $_SESSION['type']    = 'success';

            header('location: ' . BASE_URL . '/admin/users/index.php');
            exit();
        }

        // Fehlerfall → Werte erhalten
        $this->username     = $_POST['username']     ?? '';
        $this->admin        = isset($_POST['admin']) ? '1' : '0';
        $this->email        = $_POST['email']        ?? '';
        $this->password     = $_POST['password']     ?? '';
        $this->passwordConf = $_POST['passwordConf'] ?? '';
    }

    /**
     * Login verarbeiten.
     * - validateLogin()
     * - Passwort prüfen, bei Erfolg AuthService::loginUser()
     * - Bei Fehlern: Fehlermeldung + Werte behalten
     */
    public function handleLogin(): void
    {
        if (!isset($_POST['login-btn'])) {
            return;
        }

        $this->errors = validateLogin($_POST);

        if (count($this->errors) === 0) {
            $user = $this->repo->findOneByUsername($_POST['username']);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                AuthService::loginUser($user);
            } else {
                $this->errors[] = 'Falsche Eingaben';
            }
        }

        // Formwerte beibehalten
        $this->username = $_POST['username'] ?? '';
        $this->password = $_POST['password'] ?? '';
    }

    /**
     * Benutzer löschen (nur Admin).
     * - Flash + Redirect zur Liste.
     */
    public function handleDelete(): void
    {
        if (!isset($_GET['delete_id'])) {
            return;
        }

        adminOnly();

        $this->repo->delete((int) $_GET['delete_id']);

        $_SESSION['message'] = 'User wurde gelöscht';
        $_SESSION['type']    = 'success';

        header('location: ' . BASE_URL . '/admin/users/index.php');
        exit();
    }
}
