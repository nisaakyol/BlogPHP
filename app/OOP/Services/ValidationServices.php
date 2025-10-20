<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * ValidationService
 *
 * Liefert einfache Validierungen für:
 * - Topics (name, description)
 * - Posts  (title, body, topic_id)
 * - Users  (username, email, password, passwordConf)
 * - Login  (username, password)
 *
 * Rückgabewert: Array mit Fehlermeldungen (leer = valide).
 */
class ValidationService
{
    /**
     * Validiert Topic-Daten.
     *
     * @param array $data
     * @return array Fehlerliste
     */
    public static function topic(array $data): array
    {
        $errors      = [];
        $name        = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '') {
            $errors[] = 'Name ist erforderlich';
        }
        if ($description === '') {
            $errors[] = 'Description ist erforderlich';
        }

        return $errors;
    }

    /**
     * Validiert Post-Daten.
     *
     * @param array $data
     * @return array Fehlerliste
     */
    public static function post(array $data): array
    {
        $errors   = [];
        $title    = trim($data['title'] ?? '');
        $body     = trim($data['body'] ?? '');
        $topic_id = $data['topic_id'] ?? '';

        if ($title === '') {
            $errors[] = 'Title ist erforderlich';
        }
        if ($body === '') {
            $errors[] = 'Body ist erforderlich';
        }
        if ($topic_id === '' || !ctype_digit((string) $topic_id)) {
            $errors[] = 'Topic ist erforderlich';
        }

        return $errors;
    }

    /**
     * Validiert User-Daten für Registrierung/Update.
     *
     * @param array $data
     * @return array Fehlerliste
     */
    public static function user(array $data): array
    {
        $errors       = [];
        $username     = trim($data['username'] ?? '');
        $email        = trim($data['email'] ?? '');
        $password     = (string) ($data['password'] ?? '');
        $passwordConf = (string) ($data['passwordConf'] ?? '');

        if ($username === '') {
            $errors[] = 'Username ist erforderlich';
        }

        if ($email === '') {
            $errors[] = 'Email ist erforderlich';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email ist ungültig';
        }

        if ($password === '') {
            $errors[] = 'Password ist erforderlich';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password muss mindestens 6 Zeichen lang sein';
        }

        if ($passwordConf === '') {
            $errors[] = 'Password Confirmation ist erforderlich';
        } elseif ($password !== $passwordConf) {
            $errors[] = 'Passwörter stimmen nicht überein';
        }

        return $errors;
    }

    /**
     * Validiert Login-Daten.
     *
     * @param array $data
     * @return array Fehlerliste
     */
    public static function login(array $data): array
    {
        $errors = [];

        if (trim($data['username'] ?? '') === '') {
            $errors[] = 'Username ist erforderlich';
        }
        if (trim($data['password'] ?? '') === '') {
            $errors[] = 'Password ist erforderlich';
        }

        return $errors;
    }
}
