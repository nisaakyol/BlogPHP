<?php
// Zweck: CSRF-Token erzeugen & prüfen (2h Gültigkeit), schlanke Helper-Funktionen mit Session-Storage

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start(); // Session sicherstellen

function csrf_token(): string {
    // Falls noch kein Token existiert, neu erzeugen und Ablauf setzen
    if (empty($_SESSION['__csrf_token'])) {
        $_SESSION['__csrf_token']   = bin2hex(random_bytes(32)); // 64 Hex-Zeichen
        $_SESSION['__csrf_expires'] = time() + 7200;             // 2h gültig
    }
    return $_SESSION['__csrf_token'];
}

function csrf_validate_or_die(?string $token): void {
    if (session_status() === PHP_SESSION_NONE) session_start(); // Session sicherstellen

    // Token vorhanden, identisch, nicht abgelaufen
    $ok = isset($_SESSION['__csrf_token'], $_SESSION['__csrf_expires'])
       && hash_equals((string)$_SESSION['__csrf_token'], (string)$token)
       && time() <= (int)$_SESSION['__csrf_expires'];

    if (!$ok) {
        http_response_code(403); // Verboten
        throw new RuntimeException('CSRF validation failed.');
    }
}

function csrf_validate(?string $token): bool {
    // Sanfter Prüfer: true/false statt Exception
    try {
        csrf_validate_or_die($token);
        return true;
    } catch (\Throwable) {
        return false;
    }
}
