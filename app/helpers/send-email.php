<?php
declare(strict_types=1);

/**
 * Datei: app/helpers/send-email.php
 *
 * Zweck:
 *  - Einfache Admin-Mail verschicken (delegiert an MailerService)
 *  - Bei type="moderation" klickbare, signierte Approve/Reject-Links einbetten,
 *    passend zu deiner moderate.php (Parameter: action, id, exp, sig).
 */

use App\OOP\Services\MailerService;

/**
 * send_admin_mail
 *
 * $payload Felder:
 *  - type:     'moderation' | 'info' | ...
 *  - post_id:  int
 *  - title:    string
 *  - reviewer: string
 *  - note:     string (optional)
 *  - (wird bei moderation automatisch ergänzt:)
 *      - approve_url, reject_url, view_url
 *
 * @param array       $payload
 * @param string|null $to       Empfänger-Adresse (optional; sonst MAIL_TO / admin@example.com)
 * @param string|null $subject  Betreff (optional; Standard: 'Blog Benachrichtigung')
 * @return bool
 */
function send_admin_mail(array $payload, ?string $to = null, ?string $subject = null): bool
{
    // --- BASE_URL verfügbar machen ------------------------------------------
    if (!defined('BASE_URL')) {
        // Versuche path.php (definiert ROOT_PATH, BASE_URL, …)
        $path = __DIR__ . '/../../path.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
    // Fallback, falls BASE_URL immer noch fehlt (z. B. bei CLI-Test)
    if (!defined('BASE_URL')) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        // ggf. an dein Setup anpassen:
        define('BASE_URL', $scheme . '://' . $host);
    }
    $base = rtrim(BASE_URL, '/');

    // --- Empfänger & Betreff --------------------------------------------------
    $to      = $to      ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
    $subject = $subject ?? 'Blog Benachrichtigung';

    // --- Moderations-Links konstruieren (passend zu deiner moderate.php) ------
    if (($payload['type'] ?? '') === 'moderation') {
        $postId = (int)($payload['post_id'] ?? 0);

        // Secret für die Link-Signatur
        // Nutzt EMAIL_LINK_SECRET, sonst 'dev' (wie in deiner moderate.php)
        $secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';

        // Ablaufzeit (z. B. 7 Tage)
        $exp = time() + 7 * 24 * 60 * 60;

        foreach (['approve', 'reject'] as $action) {
            // Signatur entspricht deiner Prüfdatei: HMAC über "id|action|exp"
            $sig = hash_hmac('sha256', $postId . '|' . $action . '|' . $exp, $secret);

            // Pfad zu deiner Prüfdatei (bei dir im Projekt-Root: moderate.php)
            $payload[$action . '_url'] = "{$base}/moderate.php?action={$action}&id={$postId}&exp={$exp}&sig={$sig}";
        }

        // Preview-Link (signiert), damit man unveröffentlichte Beiträge ansehen kann
        $prevExp = time() + 7*24*60*60; // 7 Tage gültig
        $prevSig = hash_hmac('sha256', $postId . '|preview|' . $prevExp, $secret);
        $payload['view_url'] = "{$base}/preview.php?id={$postId}&exp={$prevExp}&sig={$prevSig}";

    }

    // --- Versand delegieren ---------------------------------------------------
    // MailerService formatiert den Payload automatisch (Key: Value-Zeilen bzw. HTML, wenn 'html' gesetzt ist)
    return MailerService::send($payload, $to, $subject);
}
