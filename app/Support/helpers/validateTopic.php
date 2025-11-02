<?php
// Zweck: Validierung für Topics (Name & Description) im Admin-Bereich

declare(strict_types=1);

// Validiert Name und optionale Description eines Topics
function validateTopic(array $data): array
{
    $errors = [];

    // Eingaben normalisieren
    $name = trim((string)($data['name'] ?? ''));
    $desc = trim((string)($data['description'] ?? ''));

    // Name prüfen
    if ($name === '') {
        $errors[] = 'Topic-Name ist erforderlich';
    } elseif (mb_strlen($name) > 120) {
        $errors[] = 'Topic-Name ist zu lang (max. 120 Zeichen)';
    }

    // Description prüfen (optional)
    if ($desc !== '' && mb_strlen($desc) > 2000) {
        $errors[] = 'Description ist zu lang (max. 2000 Zeichen)';
    }

    return $errors;
}
