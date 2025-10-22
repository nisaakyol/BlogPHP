<?php
declare(strict_types=1);

require_once ROOT_PATH . '/app/includes/bootstrap_once.php';

use App\OOP\Services\MailerService;

/**
 * send_contact
 * Verschickt eine Kontaktanfrage an den Admin.
 */
function send_contact(array $data): bool
{
    $payload = [
        'Event'   => 'Kontaktformular',
        'Name'    => trim((string)($data['name'] ?? '')),
        'Email'   => trim((string)($data['email'] ?? '')),
        'Message' => trim((string)($data['message'] ?? '')),
    ];
    $to = defined('MAIL_TO') ? MAIL_TO : 'admin@example.com';
    return MailerService::send($payload, $to, 'Neue Kontaktanfrage');
}
