<?php
declare(strict_types=1);

// Zweck: Verwalten von Benutzern (Listen, Anlegen/Registrieren, Aktualisieren, Löschen, Login) mit Legacy-kompatiblen View-Properties, Validierung und Redirects.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Services\AuthService;

class UserController
{
    // Repository-Instanz für DB-Operationen
    private UserRepository $repo;

    // Legacy-kompatible Properties für die Views/Formulare
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
        // Repo instanziieren
        $this->repo = new UserRepository();
    }

    public function bootInitialState(): void
    {
        // Alle User für Tabelle/Übersicht
        $this->admin_users = $this->repo->all();

        // Preload bei GET id → Formular für Edit füllen
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

    public function enforceHoneypotOrDie(): void
    {
        // Nur bei POST prüfen
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            // Bot-Feld gefüllt? → Abbruch
            if (!empty($_POST['honeypot'] ?? '')) {
                die('Bot-Aktivität erkannt. Der Zugriff wurde verweigert.');
            }
        }
    }

    public function handleRegisterOrCreateAdmin(): void
    {
        // Ohne Submit-Flag nichts tun
        if (!isset($_POST['register-btn']) && !isset($_POST['create-admin'])) {
            return;
        }

        // Eingaben validieren (Helper)
        $this->errors = validateUser($_POST);

        if (count($this->errors) === 0) {
            // Steuerfelder entfernen
            unset($_POST['register-btn'], $_POST['passwordConf'], $_POST['create-admin'], $_POST['honeypot']);

            // Passwort hashen
            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Adminanlage
            if (isset($_POST['admin'])) {
                $_POST['admin'] = 1;

                $this->repo->create($_POST);

                $_SESSION['message'] = 'Admin user erfolgreich erstellt';
                $_SESSION['type']    = 'success';

                header('location: ' . BASE_URL . '/admin/users/index.php');
                exit();
            }

            // Normale Registrierung
            $_POST['admin'] = 0;

            $user_id = $this->repo->create($_POST);
            $user    = $this->repo->findById($user_id);

            // Automatisches Einloggen
            AuthService::loginUser($user);
        } else {
            // Fehler → Formwerte erhalten (außer Hash)
            $this->username     = $_POST['username']     ?? '';
            $this->admin        = isset($_POST['admin']) ? '1' : '0';
            $this->email        = $_POST['email']        ?? '';
            $this->password     = $_POST['password']     ?? '';
            $this->passwordConf = $_POST['passwordConf'] ?? '';
        }
    }

    public function handleUpdateUser(): void
    {
        // Ohne Submit-Flag nichts tun
        if (!isset($_POST['update-user'])) {
            return;
        }

        // Nur Admins
        adminOnly();

        // Prüfen
        $this->errors = validateUser($_POST);

        if (count($this->errors) === 0) {
            $id = (int) $_POST['id'];

            // Steuerfelder entfernen
            unset($_POST['passwordConf'], $_POST['update-user'], $_POST['id']);

            // Passwort neu setzen + Admin-Flag normalisieren
            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $_POST['admin']    = isset($_POST['admin']) ? 1 : 0;

            // Update ausführen
            $this->repo->update($id, $_POST);

            // Legacy-Flash (inkl. Tippfehler beibehalten)
            $_SESSION['message'] = 'Admin user erfoglreich erstellt';
            $_SESSION['type']    = 'success';

            header('location: ' . BASE_URL . '/admin/users/index.php');
            exit();
        }

        // Fehler → Werte erhalten
        $this->username     = $_POST['username']     ?? '';
        $this->admin        = isset($_POST['admin']) ? '1' : '0';
        $this->email        = $_POST['email']        ?? '';
        $this->password     = $_POST['password']     ?? '';
        $this->passwordConf = $_POST['passwordConf'] ?? '';
    }

    public function handleLogin(): void
    {
        // Ohne Submit-Flag nichts tun
        if (!isset($_POST['login-btn'])) {
            return;
        }

        // Eingaben prüfen
        $this->errors = validateLogin($_POST);

        if (count($this->errors) === 0) {
            // User per Username laden
            $user = $this->repo->findOneByUsername($_POST['username']);

            // Passwort prüfen und ggf. einloggen
            if ($user && password_verify($_POST['password'], $user['password'])) {
                AuthService::loginUser($user);
            } else {
                $this->errors[] = 'Falsche Eingaben';
            }
        }

        // Formwerte beibehalten (Passwort nur für Re-Rendering)
        $this->username = $_POST['username'] ?? '';
        $this->password = $_POST['password'] ?? '';
    }

    public function handleDelete(): void
    {
        // Ohne GET-Flag nichts tun
        if (!isset($_GET['delete_id'])) {
            return;
        }

        // Nur Admins
        adminOnly();

        // Löschen
        $this->repo->delete((int) $_GET['delete_id']);

        // Flash + Redirect
        $_SESSION['message'] = 'User wurde gelöscht';
        $_SESSION['type']    = 'success';

        header('location: ' . BASE_URL . '/admin/users/index.php');
        exit();
    }
}
