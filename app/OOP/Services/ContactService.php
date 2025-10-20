<?php
declare(strict_types=1);

namespace App\OOP\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * ContactService
 *
 * Versendet Nachrichten aus dem Kontaktformular.
 * - Konfiguration wird aus /config/mail.php geladen (falls vorhanden),
 *   ansonsten werden Legacy-Defaults verwendet.
 * - Absenderadresse stammt direkt aus dem Formular ($from).
 */
class ContactService
{
    /**
     * Sendet die Kontaktmail.
     *
     * @param string $from Vom Nutzer eingegebene Absenderadresse (setFrom)
     * @param string $text Inhalt der Nachricht
     * @return bool true bei Erfolg, sonst false
     */
    public static function send(string $from, string $text): bool
    {
        // Legacy-Defaults (überschreibbar via config/mail.php)
        $cfg = [
            'host'     => 'smtp.gmail.com',
            'username' => 'contactusdhbwblog@gmail.com',
            'password' => 'ywsiyetgqcrxzgts',
            'port'     => 587,
            'secure'   => 'tls',
            'to'       => 'sammelstelledhbwblog@gmail.com',
        ];

        $root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../../..');
        $file = $root . '/config/mail.php';

        if (is_file($file)) {
            $loaded = require $file;
            if (is_array($loaded)) {
                $cfg = array_replace($cfg, $loaded);
            }
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP-Basis
            $mail->isSMTP();
            $mail->Host       = (string) $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) $cfg['username'];
            $mail->Password   = (string) $cfg['password'];
            $mail->SMTPSecure = (string) $cfg['secure'];
            $mail->Port       = (int) $cfg['port'];

            // Absender (vom User) und Empfänger (Sammelstelle)
            $mail->setFrom($from);
            $mail->addAddress((string) $cfg['to']);

            // Inhalt
            $mail->Body = "Die folgende Email-Adresse: {$from}\n\n"
                        . "hat ihnen über das Kontakt Formular mitgeteilt:\n\n"
                        . "{$text}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Verhalten wie Legacy: kein Throw, nur Log
            error_log('[ContactService] Mail send failed: ' . $e->getMessage());
            return false;
        }
    }
}
