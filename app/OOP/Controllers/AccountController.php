<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class AccountController
{
    public function __construct(private DbRepository $repo) {}

    /** Seite anzeigen (optional: Username/Email für die Anzeige) */
    public function showChangePassword(int $userId): array
    {
        $user = $this->repo->selectOne('users', ['id' => $userId]);
        if (!$user) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        // nur was wir evtl. anzeigen möchten
        return [
            'user' => [
                'id'       => (int)$user['id'],
                'username' => (string)$user['username'],
                'email'    => (string)$user['email'],
            ],
            'errors' => [],
        ];
    }

    /** Passwort ändern */
    public function updatePassword(int $userId, array $data): void
    {
        // Eingaben
        $current = (string)($data['current_password'] ?? '');
        $new     = (string)($data['new_password'] ?? '');
        $confirm = (string)($data['new_password_confirmation'] ?? '');

        $errors = [];

        // User laden (inkl. Hash)
        $user = $this->repo->selectOne('users', ['id' => $userId]);
        if (!$user) {
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Validierung
        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Bitte alle Felder ausfüllen.';
        }
        if (!password_verify($current, (string)$user['password'])) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'Neues Passwort muss mindestens 6 Zeichen haben.';
        }
        if ($current !== '' && hash_equals($current, $new)) {
            $errors[] = 'Neues Passwort darf nicht dem alten entsprechen.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            // kein "old" nötig – wir befüllen Passwörter aus Sicherheitsgründen nicht vor
            header('Location: ' . BASE_URL . '/user/password.php');
            exit;
        }

        // Speichern
        $this->repo->update('users', $userId, [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);

        // Optional: Session härten (z. B. Re-Login empfehlen)
        $_SESSION['message'] = 'Passwort erfolgreich geändert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/user/password.php');
        exit;
    }
}
