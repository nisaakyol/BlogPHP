<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
    if (empty($_SESSION['csrf']) || !is_array($_SESSION['csrf'])) {
        $_SESSION['csrf'] = [];
    }
    // Nur erzeugen, wenn keins vorhanden ist
    if (empty($_SESSION['csrf']['token'])) {
        $_SESSION['csrf']['token']   = bin2hex(random_bytes(32));
        $_SESSION['csrf']['expires'] = time() + 7200; // 2 Stunden
    }
    return $_SESSION['csrf']['token'];
}

function csrf_validate_or_die(?string $token): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $ok = isset($_SESSION['csrf']['token'], $_SESSION['csrf']['expires'])
       && hash_equals((string)$_SESSION['csrf']['token'], (string)$token)
       && time() <= (int)$_SESSION['csrf']['expires'];

    if (!$ok) {
        // Kein Redirect hier erzwungen – aufrufende Seite kann selbst umleiten
        http_response_code(403);
        throw new RuntimeException('CSRF validation failed.');
    }
}

/** Optional: bool-Variante */
function csrf_validate(?string $token): bool {
    try { csrf_validate_or_die($token); return true; } catch (\Throwable) { return false; }
}
