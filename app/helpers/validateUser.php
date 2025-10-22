<?php
declare(strict_types=1);

/**
 * validateUser
 * Minimaler Validator (ohne irgendwelche Includes).
 *
 * @param array $data
 * @param bool  $isUpdate  Bei Updates darf das Passwort leer bleiben
 * @return array<string>
 */
function validateUser(array $data, bool $isUpdate = false): array
{
    $errors = [];

    $username = trim((string)($data['username'] ?? ''));
    $email    = trim((string)($data['email']    ?? ''));
    $pass     = (string)($data['password']      ?? '');
    $pass2    = (string)($data['passwordConf']  ?? '');

    if ($username === '' || mb_strlen($username) < 3) {
        $errors[] = 'Username muss mindestens 3 Zeichen haben';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte gültige E-Mail angeben';
    }

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
