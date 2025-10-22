<?php
declare(strict_types=1);

use App\OOP\Services\MailerService;

/**
 * Schickt eine einfache Admin-Mail (ohne Bootstrap-Includes).
 *
 * @param array       $payload  z. B. ['Event'=>'...', 'Title'=>'...', 'User'=>'...']
 * @param string|null $to       Standard ist MAIL_TO oder admin@example.com
 * @param string|null $subject  Standard "Blog Benachrichtigung"
 */
function send_admin_mail(array $payload, ?string $to = null, ?string $subject = null): bool
{
    $to      = $to      ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
    $subject = $subject ?? 'Blog Benachrichtigung';
    return MailerService::send($payload, $to, $subject);
}
