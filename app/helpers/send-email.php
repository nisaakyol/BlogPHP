<?php
declare(strict_types=1);

use App\OOP\Services\MailerService;

/**
 *
 * @param array       $payload  Frei gestaltbares Daten-Array
 * @param string|null $to       Empfänger (Default: MAIL_TO oder admin@example.com)
 * @param string|null $subject  Betreff   (Default: "Blog Benachrichtigung")
 * @return bool
 */
function send_admin_mail(array $payload, ?string $to = null, ?string $subject = null): bool
{
    // --- BASE_URL sicherstellen ----------------------------------------------
    if (!defined('BASE_URL')) {
        // Versuch: zentrales path.php laden (definiert ROOT_PATH, BASE_URL, …)
        $path = __DIR__ . '/../../path.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
    // Fallback (CLI/Tests)
    if (!defined('BASE_URL')) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        define('BASE_URL', $scheme . '://' . $host);
    }
    $base = rtrim(BASE_URL, '/');

    // --- Empfänger & Betreff --------------------------------------------------
    $to      = $to      ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
    $subject = $subject ?? 'Blog Benachrichtigung';

    // --- Moderations-Links (signiert) -----------------------------------------
    if (($payload['type'] ?? '') === 'moderation') {
        $postId = (int)($payload['post_id'] ?? 0);

        // Secret wie in moderate.php
        $secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';

        // Ablaufzeit (z. B. 7 Tage)
        $exp = time() + 7 * 24 * 60 * 60;

        foreach (['approve', 'reject'] as $action) {
            // HMAC über "id|action|exp"
            $sig = hash_hmac('sha256', $postId . '|' . $action . '|' . $exp, $secret);
            $payload[$action . '_url'] = "{$base}/moderate.php?action={$action}&id={$postId}&exp={$exp}&sig={$sig}";
        }

        // Preview (signiert)
        $prevExp = time() + 7 * 24 * 60 * 60;
        $prevSig = hash_hmac('sha256', $postId . '|preview|' . $prevExp, $secret);
        $payload['view_url'] = "{$base}/preview.php?id={$postId}&exp={$prevExp}&sig={$prevSig}";

        // Explizit unterdrücken:
        unset($payload['reviewer']);
        if (!isset($payload['note']) || $payload['note'] === '' || $payload['note'] === null) {
            unset($payload['note']);
        }
    }

    // --- Versand delegieren ---------------------------------------------------
    // MailerService formatiert das Array als Plaintext (oder HTML, wenn 'html' gesetzt ist)
    return MailerService::send($payload, $to, $subject);
}
