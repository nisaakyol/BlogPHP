<?php
// Zweck: Zentrales Helper-Wrapper für Mailversand an Admin inkl. Moderations-/Preview-Links

declare(strict_types=1);

require_once ROOT_PATH . '/app/Infrastructure/Services/MailerService.php';
use App\Infrastructure\Services\MailerService;

/**
 * @param array       $payload  Frei gestaltbares Daten-Array
 * @param string|null $to       Empfänger (Default: MAIL_TO oder admin@example.com)
 * @param string|null $subject  Betreff   (Default: "Blog Benachrichtigung")
 * @return bool
 */
function send_admin_mail(array $payload, ?string $to = null, ?string $subject = null): bool
{
    // BASE_URL sicherstellen
    if (!defined('BASE_URL')) {
        // robust: mehrere Kandidaten für path.php versuchen
        $candidates = [
            __DIR__ . '/../../../public/path.php', // üblich: public/path.php
            __DIR__ . '/../../path.php',           // Fallback: falls path.php im Projektroot liegt
        ];
        foreach ($candidates as $p) {
            if (is_file($p)) { require_once $p; break; }
        }
    }

    // Notfall-BASE_URL bauen, falls path.php nicht gefunden wurde
    if (!defined('BASE_URL')) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        define('BASE_URL', $scheme . '://' . $host);
    }

    // Konsistente Basis-URLs
    $base       = rtrim(BASE_URL, '/');
    $basePublic = $base . '/public';

    // Empfänger/Betreff Defaults
    $to      = $to      ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
    $subject = $subject ?? 'Blog Benachrichtigung';

    // Moderations-Payload: Approve/Reject + Preview-Links hinzufügen
    if (($payload['type'] ?? '') === 'moderation') {
        $postId = (int)($payload['post_id'] ?? 0);
        $secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
        $exp    = time() + 7 * 24 * 60 * 60;

        foreach (['approve', 'reject'] as $action) {
            $sig = hash_hmac('sha256', $postId . '|' . $action . '|' . $exp, $secret);
            // Hinweis: Script liegt in /public/admin/posts/moderate.php
            $payload[$action . '_url'] = "{$basePublic}/admin/posts/moderate.php?action={$action}&id={$postId}&exp={$exp}&sig={$sig}";
        }

        $prevExp = time() + 7 * 24 * 60 * 60;
        $prevSig = hash_hmac('sha256', $postId . '|preview|' . $prevExp, $secret);
        $payload['view_url'] = "{$basePublic}/preview.php?id={$postId}&exp={$prevExp}&sig={$prevSig}";

        // Aufräumen optionaler Felder
        unset($payload['reviewer']);
        if (empty($payload['note'])) unset($payload['note']);
    }

    return MailerService::send($payload, $to, $subject);
}
