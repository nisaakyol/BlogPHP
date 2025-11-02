<?php
declare(strict_types=1);

// Zweck: Controller zum Anzeigen des Passwort-Ändern-Formulars und zum Aktualisieren des Benutzerpassworts.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\DbRepository;

final class AccountController
{
    // Repository wird injiziert und für DB-Zugriffe genutzt
    public function __construct(private DbRepository $repo) {}

    // Seite anzeigen (liest User-Daten für die Anzeige; liefert View-Model)
    public function showChangePassword(int $userId): array
    {
        // Benutzer aus DB laden
        $user = $this->repo->selectOne('users', ['id' => $userId]);
        if (!$user) {
            // Fehlernachricht und Redirect zur Startseite
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Nur benötigte Felder für die View zurückgeben
        return [
            'user' => [
                'id'       => (int)$user['id'],
                'username' => (string)$user['username'],
                'email'    => (string)$user['email'],
            ],
            'errors' => [],
        ];
    }

    // Passwort ändern (validiert Eingaben, prüft altes Passwort und speichert neuen Hash)
    public function updatePassword(int $userId, array $data): void
    {
        // Eingaben aus Request lesen
        $current = (string)($data['current_password'] ?? '');
        $new     = (string)($data['new_password'] ?? '');
        $confirm = (string)($data['new_password_confirmation'] ?? '');

        // Fehlerliste initialisieren
        $errors = [];

        // Benutzer inkl. Passwort-Hash laden
        $user = $this->repo->selectOne('users', ['id' => $userId]);
        if (!$user) {
            // Benutzer existiert nicht → Abbruch mit Meldung
            $_SESSION['message'] = 'Benutzer nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        // Pflichtfelder prüfen
        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Bitte alle Felder ausfüllen.';
        }

        // Altes Passwort verifizieren
        if (!password_verify($current, (string)$user['password'])) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        }

        // Neues Passwort und Bestätigung müssen übereinstimmen
        if ($new !== $confirm) {
            $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
        }

        // Mindestlänge prüfen
        if (strlen($new) < 6) {
            $errors[] = 'Neues Passwort muss mindestens 6 Zeichen haben.';
        }

        // Neues Passwort darf nicht identisch mit dem alten Klartext sein
        if ($current !== '' && hash_equals($current, $new)) {
            $errors[] = 'Neues Passwort darf nicht dem alten entsprechen.';
        }

        // Bei Fehlern: zurück zur Formularseite mit Fehlermeldungen
        if ($errors) {
            $_SESSION['errors'] = $errors;
            // Passwörter werden aus Sicherheitsgründen nicht vorbefüllt
            header('Location: ' . BASE_URL . '/user/password.php');
            exit;
        }

        // Neuen Passwort-Hash speichern
        $this->repo->update('users', $userId, [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);

        // Erfolgsmeldung und Redirect zurück zur Passwort-Seite
        $_SESSION['message'] = 'Passwort erfolgreich geändert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/user/password.php');
        exit;
    }
}
