<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

final class AdminUserController
{
    public function __construct(private DbRepository $repo) {}

    /** Liste für admin/users/index.php */
    public function index(): array
    {
        $this->ensureAdmin();
        return [
            'admin_users' => $this->repo->selectAll('users', [], 'id ASC'),
        ];
    }

    /** Edit-Formulardaten laden */
    public function edit(int $id): array
    {
        $this->ensureAdmin();

        $user = $this->repo->selectOne('users', ['id' => $id]);
        if (!$user) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/users/index.php');
            exit;
        }

        // Nur die Felder, die wir im Formular brauchen
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

    /** Update vom Edit-Formular */
    public function update(int $id, array $data): void
    {
        $this->ensureAdmin();

        // Aktuellen Datensatz laden (auch um vorhandene Spalten zu sehen)
        $current = $this->repo->selectOne('users', ['id' => $id]);
        if (!$current) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/users/index.php');
            exit;
        }

        $username = trim((string)($data['username'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));
        $pass     = (string)($data['password'] ?? '');
        $pass2    = (string)($data['passwordConf'] ?? '');
        $isAdmin  = isset($data['admin']) && (int)$data['admin'] === 1 ? 1 : 0;

        $errors = [];
        if ($username === '') $errors[] = 'Username ist erforderlich.';
        if ($email === '')    $errors[] = 'Email ist erforderlich.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email ist ungültig.';

        if ($pass !== '' || $pass2 !== '') {
            if ($pass !== $pass2) {
                $errors[] = 'Passwörter stimmen nicht überein.';
            } elseif (strlen($pass) < 6) {
                $errors[] = 'Passwort muss mindestens 6 Zeichen haben.';
            }
        }

        // Eindeutigkeit (ohne sich selbst)
        $u = $this->repo->selectOne('users', ['username' => $username]);
        if ($u && (int)$u['id'] !== $id) $errors[] = 'Username ist bereits vergeben.';
        $e = $this->repo->selectOne('users', ['email' => $email]);
        if ($e && (int)$e['id'] !== $id) $errors[] = 'Email ist bereits vergeben.';

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = [
                'username' => $username,
                'email'    => $email,
                'admin'    => $isAdmin,
            ];
            header('Location: ' . BASE_URL . '/admin/users/edit.php?id=' . $id);
            exit;
        }

        // Update-Payload
        $payload = [
            'username' => $username,
            'email'    => $email,
        ];

        // Admin-Flag nur setzen, wenn die Spalte in diesem Schema existiert
        if (array_key_exists('admin', $current)) {
            $payload['admin'] = $isAdmin;
        }

        // Passwort optional ändern
        if ($pass !== '') {
            $payload['password'] = password_hash($pass, PASSWORD_DEFAULT);
        }

        $this->repo->update('users', $id, $payload);

        $_SESSION['message'] = 'Benutzer wurde aktualisiert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit;
    }


    /** Löschen (für index.php?delete_id=...) */
    public function delete(int $id): void
    {
        $this->ensureAdmin();

        // Selbst-Löschung verhindern (optional)
        if (!empty($_SESSION['id']) && (int)$_SESSION['id'] === $id) {
            $_SESSION['message'] = 'Du kannst dich nicht selbst löschen.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/users/index.php');
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
            if ($e->getCode() === '23000') {
                $_SESSION['message'] = 'Löschen nicht möglich: verknüpfte Daten vorhanden.';
                $_SESSION['type']    = 'error';
            } else {
                $_SESSION['message'] = 'Fehler beim Löschen: ' . $e->getMessage();
                $_SESSION['type']    = 'error';
            }
        }

        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit;
    }

    // ──────────────────────────────────────────────────────────

    private function ensureAdmin(): void
    {
        // passe an deine Session-Flags an
        if (!isset($_SESSION['id']) || empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nur Admins haben Zugriff.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
}
