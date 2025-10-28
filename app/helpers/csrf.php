<?php
declare(strict_types=1);

/**
 * CSRF-Helfer (Session-basiertes Token)
 * - csrf_token():    gibt ein Token zurück (legt es in der Session an, falls nicht vorhanden)
 * - csrf_validate(): prüft ein Token und gibt bool zurück
 * - csrf_validate_or_die(): prüft und beendet mit 403, wenn ungültig
 * - csrf_field():    convenience helper für <input hidden ...>
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const CSRF_SESSION_KEY = '_csrf_token';

/** Erzeuge/lese das CSRF-Token für diese Session. */
function csrf_token(): string {
    if (empty($_SESSION[CSRF_SESSION_KEY])) {
        // 32 Bytes kryptografisch zufällige Daten → 64 hex chars
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION[CSRF_SESSION_KEY];
}

/** Prüfe ein eingereichtes Token. */
function csrf_validate(?string $token): bool {
    if (!isset($_SESSION[CSRF_SESSION_KEY])) return false;
    if (!is_string($token) || $token === '') return false;
    // Zeitkonstante Prüfung
    return hash_equals($_SESSION[CSRF_SESSION_KEY], $token);
}

/** Prüfe Token, sonst mit 403 abbrechen. */
function csrf_validate_or_die(?string $token): void {
    if (!csrf_validate($token)) {
        http_response_code(403);
        echo 'CSRF validation failed.';
        exit;
    }
}

/** Komfort: Hidden-Input fürs Formular. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}
