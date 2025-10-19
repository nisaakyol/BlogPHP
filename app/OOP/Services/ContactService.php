<?php
namespace App\OOP\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactService
{
    /**
     * Sendet die Kontaktmail.
     * $from: die vom Nutzer eingegebene Adresse (setFrom)
     * $text: Nachrichtentext aus dem Formular
     *
     * SMTP-Defaults spiegeln deinen Legacy-Stand. Überschreibbar via config/mail.php.
     */
    public static function send(string $from, string $text): bool
    {
        // Config aus optionaler Datei ziehen
        $cfg = [
            'host'     => 'smtp.gmail.com',
            'username' => 'contactusdhbwblog@gmail.com',
            'password' => 'ywsiyetgqcrxzgts',
            'port'     => 587,
            'secure'   => 'tls',
            'to'       => 'sammelstelledhbwblog@gmail.com',
        ];
        $file = (defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../../..')) . '/config/mail.php';
        if (is_file($file)) {
            $loaded = require $file;
            if (is_array($loaded)) $cfg = array_replace($cfg, $loaded);
        }

        $mail = new PHPMailer(true);
        try {
            // SMTP
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['secure'];
            $mail->Port       = (int)$cfg['port'];

            // Absender (vom User)
            $mail->setFrom($from);
            // Empfänger (Sammelstelle)
            $mail->addAddress($cfg['to']);

            $mail->Body = "Die folgende Email-Adresse: {$from}\n\n hat ihnen über das Kontakt Formular mitgeteilt: \n\n {$text}";
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Keine Exception nach außen werfen – Verhalten still halten (wie Legacy)
            error_log('[ContactService] Mail send failed: ' . $e->getMessage());
            return false;
        }
    }
}
