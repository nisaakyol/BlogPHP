<?php
declare(strict_types=1);

// Zweck: Minimal-Validierung für Benutzer (Create/Update) ohne externe Includes

function validateUser(array $data, bool $isUpdate = false): array
{
    $errors = [];

    // Felder normalisieren
    $username = trim((string)($data['username'] ?? ''));
    $email    = trim((string)($data['email']    ?? ''));
    $pass     = (string)($data['password']      ?? '');
    $pass2    = (string)($data['passwordConf']  ?? '');

    // Username prüfen
    if ($username === '' || mb_strlen($username) < 3) {
        $errors[] = 'Username muss mindestens 3 Zeichen haben';
    }

    // E-Mail prüfen
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte gültige E-Mail angeben';
    }

    // Passwortregeln: bei Create immer, bei Update nur wenn eines der Felder gesetzt ist
    if (!$isUpdate || $pass !== '' || $pass2 !== '') {
        if ($pass === '') {
            $errors[] = 'Passwort ist erforderlich';
        } elseif (strlen($pass) < 6) {
            $errors[] = 'Passwort muss mindestens 6 Zeichen haben';
        }
        if ($pass !== $pass2) {
            $errors[] = 'Passwörter stimmen nicht überein';
        }
    }

    return $errors;
}
