<?php
// Zweck: Validierung von Title, Body, Topic und optional Bild beim Erstellen/Aktualisieren von Posts

declare(strict_types=1);

// Validiere Title, Body, Topic und (optional) Bild.
function validatePost(array $data, ?array $files = null, bool $requireImageOnCreate = true): array
{
    $errors = [];

    $title    = trim((string)($data['title']   ?? ''));
    $body     = trim((string)($data['body']    ?? ''));
    $topicRaw = (string)($data['topic_id']     ?? '');

    // Titel prüfen
    if ($title === '') {
        $errors[] = 'Title ist erforderlich';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title ist zu lang (max. 255 Zeichen)';
    }

    // Body prüfen
    if ($body === '') {
        $errors[] = 'Body ist erforderlich';
    }

    // Topic prüfen (muss positive Ganzzahl sein)
    if ($topicRaw === '' || !ctype_digit($topicRaw) || (int)$topicRaw <= 0) {
        $errors[] = 'Bitte ein gültiges Topic auswählen';
    }

    // Bild nur beim Erstellen erzwingen (falls konfiguriert)
    $isCreate = isset($data['add-post']); // Flag aus Create-Form
    if ($requireImageOnCreate && $isCreate) {
        $img = $files['image'] ?? null;
        if (!$img || empty($img['name'])) {
            $errors[] = 'Post image required';
        }
    }

    // Bild + ALT/CAP
    $hasNewImage = !empty($files['image']['name'] ?? '');
    $hasImageRef = !empty($data['current_image'] ?? '');

    $alt = trim((string)($data['image_alt'] ?? ''));
    $cap = trim((string)($data['image_caption'] ?? ''));

    if (($hasNewImage || $hasImageRef) && $alt === '') {
        $errors[] = 'Bitte eine Bildbeschreibung (ALT-Text) angeben.';
    }

    if ($alt !== '' && mb_strlen($alt) > 200) {
        $errors[] = 'Die Bildbeschreibung (ALT) darf max. 200 Zeichen haben.';
    }

    if ($cap !== '' && mb_strlen($cap) > 300) {
        $errors[] = 'Die Bildunterschrift darf max. 300 Zeichen haben.';
    }

    return $errors;
}
