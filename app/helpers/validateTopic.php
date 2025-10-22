<?php
declare(strict_types=1);

function validateTopic(array $data): array
{
    $errors = [];

    $name = trim((string)($data['name'] ?? ''));
    $desc = trim((string)($data['description'] ?? ''));

    if ($name === '') {
        $errors[] = 'Topic-Name ist erforderlich';
    } elseif (mb_strlen($name) > 120) {
        $errors[] = 'Topic-Name ist zu lang (max. 120 Zeichen)';
    }

    if ($desc !== '' && mb_strlen($desc) > 2000) {
        $errors[] = 'Description ist zu lang (max. 2000 Zeichen)';
    }

    return $errors;
}
