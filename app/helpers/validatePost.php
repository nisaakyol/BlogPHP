<?php
declare(strict_types=1);

/**
 * Datei: app/helpers/validatePost.php
 * Zweck: Minimal-Validator für Posts (ohne Autoload/Includes)
 *
 * Aufruf:
 *   $errors = validatePost($_POST, $_FILES);            // beim Create
 *   $errors = validatePost($_POST, $_FILES, false);     // beim Update (Bild optional)
 */

/**
 * Validiere Title, Body, Topic und (optional) Bild.
 *
 * @param array      $data   typ. $_POST
 * @param array|null $files  typ. $_FILES
 * @param bool       $requireImageOnCreate  Wenn true und "add-post" gesetzt, ist ein Bild Pflicht
 * @return array<string> Fehlerliste
 */
function validatePost(array $data, ?array $files = null, bool $requireImageOnCreate = true): array
{
    $errors = [];

    $title    = trim((string)($data['title']    ?? ''));
    $body     = trim((string)($data['body']     ?? ''));
    $topicRaw = (string)($data['topic_id']      ?? '');

    // Titel
    if ($title === '') {
        $errors[] = 'Title ist erforderlich';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title ist zu lang (max. 255 Zeichen)';
    }

    // Body
    if ($body === '') {
        $errors[] = 'Body ist erforderlich';
    }

    // Topic (positive Ganzzahl)
    if ($topicRaw === '' || !ctype_digit($topicRaw) || (int)$topicRaw <= 0) {
        $errors[] = 'Bitte ein gültiges Topic auswählen';
    }

    // Bild: nur beim Erstellen erzwingen (wenn gewünscht)
    $isCreate = isset($data['add-post']);
    if ($requireImageOnCreate && $isCreate) {
        $img = $files['image'] ?? null;
        if (!$img || empty($img['name'])) {
            $errors[] = 'Post image required';
        }
    }

    return $errors;
}
