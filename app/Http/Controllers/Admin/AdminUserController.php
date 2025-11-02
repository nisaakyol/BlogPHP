<?php
declare(strict_types=1);

// Zweck: Admin-Controller zum Auflisten, Bearbeiten, Aktualisieren und Löschen von Benutzern mit Validierung, Flash-Messages und Zugriffsschutz.

namespace App\Http\Controllers\Admin;

use App\Infrastructure\Repositories\DbRepository;

final class AdminUserController
{
    // Repo per Konstruktor-Injection
    public function __construct(private DbRepository $repo) {}

    // Liste aller User für die Admin-Übersicht
    public function index(): array
    {
        $this->ensureAdmin(); // Zugriffsschutz
        return [
            'admin_users' => $this->repo->selectAll('users', [], 'id ASC'),
        ];
    }

    // Edit-Formular: User laden und ViewModel bereitstellen
    public function edit(int $id): array
    {
        $this->ensureAdmin();

        $user = $this->repo->selectOne('users', ['id' => $id]);
        if (!$user) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/admin/users/index.php');
            exit;
        }

        // Nur benötigte Felder an die View geben
        $vm = [
            'user'   => [
                'id'       => (int)$user['id'],
                'username' => (string)$user['username'],
                'email'    => (string)$user['email'],
                'admin'    => isset($user['admin']) ? (int)$user['admin'] : 0,
            ],
            'errors' => [],
        ];
        return $vm;
    }

    // Update-Verarbeitung für einen User
    public function update(int $id, array $data): void
    {
        $this->ensureAdmin();

        // Aktuellen Datensatz holen (u. a. um Spalten zu kennen)
        $current = $this->repo->selectOne('users', ['id' => $id]);
        if (!$current) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/admin/users/index.php');
            exit;
        }

        // Eingaben lesen
        $username = trim((string)($data['username'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));
        $pass     = (string)($data['password'] ?? '');
        $pass2    = (string)($data['passwordConf'] ?? '');
        $isAdmin  = isset($data['admin']) && (int)$data['admin'] === 1 ? 1 : 0;

        // Validierung
        $errors = [];
        if ($username === '') $errors[] = 'Username ist erforderlich.';
        if ($email === '')    $errors[] = 'Email ist erforderlich.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email ist ungültig.';

        // Passwort-Checks nur wenn etwas eingegeben wurde
        if ($pass !== '' || $pass2 !== '') {
            if ($pass !== $pass2) {
                $errors[] = 'Passwörter stimmen nicht überein.';
            } elseif (strlen($pass) < 6) {
                $errors[] = 'Passwort muss mindestens 6 Zeichen haben.';
            }
        }

        // Eindeutigkeit prüfen (Username/Email, ohne sich selbst)
        $u = $this->repo->selectOne('users', ['username' => $username]);
        if ($u && (int)$u['id'] !== $id) $errors[] = 'Username ist bereits vergeben.';
        $e = $this->repo->selectOne('users', ['email' => $email]);
        if ($e && (int)$e['id'] !== $id) $errors[] = 'Email ist bereits vergeben.';

        // Fehler → zurück zum Formular
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = [
                'username' => $username,
                'email'    => $email,
                'admin'    => $isAdmin,
            ];
            header('Location: ' . BASE_URL . '/public/admin/users/edit.php?id=' . $id);
            exit;
        }

        // Update-Payload bauen
        $payload = [
            'username' => $username,
            'email'    => $email,
        ];

        // Admin-Flag nur setzen, wenn Spalte existiert (Schema-kompatibel)
        if (array_key_exists('admin', $current)) {
            $payload['admin'] = $isAdmin;
        }

        // Passwort optional aktualisieren
        if ($pass !== '') {
            $payload['password'] = password_hash($pass, PASSWORD_DEFAULT);
        }

        // Update ausführen
        $this->repo->update('users', $id, $payload);

        // Erfolgsmeldung + Redirect
        $_SESSION['message'] = 'Benutzer wurde aktualisiert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/admin/users/index.php');
        exit;
    }

    // Benutzer löschen (mit Selbstschutz und FK-Fehlerbehandlung)
    public function delete(int $id): void
    {
        $this->ensureAdmin();

        // Verhindert Selbst-Löschung
        if (!empty($_SESSION['id']) && (int)$_SESSION['id'] === $id) {
            $_SESSION['message'] = 'Du kannst dich nicht selbst löschen.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/admin/users/index.php');
            exit;
        }

        try {
            $affected = $this->repo->delete('users', $id);
            if ($affected > 0) {
                $_SESSION['message'] = 'User wurde gelöscht';
                $_SESSION['type']    = 'success';
            } else {
                $_SESSION['message'] = 'User nicht gefunden – nichts gelöscht.';
                $_SESSION['type']    = 'error';
            }
        } catch (\PDOException $e) {
            // FK-Constraint (23000) gesondert behandeln
            if ($e->getCode() === '23000') {
                $_SESSION['message'] = 'Löschen nicht möglich: verknüpfte Daten vorhanden.';
                $_SESSION['type']    = 'error';
            } else {
                $_SESSION['message'] = 'Fehler beim Löschen: ' . $e->getMessage();
                $_SESSION['type']    = 'error';
            }
        }

        header('Location: ' . BASE_URL . '/public/admin/users/index.php');
        exit;
    }

    // Guard: erlaubt nur Admins den Zugriff
    private function ensureAdmin(): void
    {
        // Prüft Session-Flags und leitet bei Bedarf um
        if (!isset($_SESSION['id']) || empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nur Admins haben Zugriff.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/index.php');
            exit;
        }
    }
}
