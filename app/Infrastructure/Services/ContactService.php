<?php
declare(strict_types=1);

namespace App\Infrastructure\Services;

// Zweck: Kontaktformular-Nachrichten per SMTP versenden (Konfig aus /config/mail.php überschreibbar)
class ContactService
{
    // Sendet die Kontaktmail; $from = Absender aus Formular (Reply-To), $text = Nachrichtentext
    public static function send(string $from, string $text): bool
    {
        // Defaults (können in /config/mail.php überschrieben werden)
        $cfg = [
            'host'     => 'smtp.gmail.com',
            'username' => 'blog@gmail.com',
            'password' => 'ywsiyetgqcrxzgts',
            'port'     => 587,
            'secure'   => 'tls',
            'to'       => 'travelblogblog@gmail.com',
            'from'     => 'no-reply@blog.local', // technischer From, um DMARC-Fehler zu vermeiden
            'fromName' => 'Blog Kontakt',
            'subject'  => 'Kontaktformular',
        ];

        // Konfig laden (falls vorhanden)
        $root = \defined('ROOT_PATH') ? ROOT_PATH : \realpath(__DIR__ . '/../../..');
        $file = $root . '/config/mail.php';
        if (\is_file($file)) {
            $loaded = require $file;
            if (\is_array($loaded)) {
                $cfg = \array_replace($cfg, $loaded);
            }
        }

        // Eingaben glätten
        $from = \trim($from);
        $text = \trim($text);

        // Einfache E-Mail-Prüfung
        $isEmail = (bool)\filter_var($from, FILTER_VALIDATE_EMAIL);
        if (!$isEmail) {
            // Fallback: keine gültige Absenderadresse → als Text anhängen
            $from = '';
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP-Basis
            $mail->isSMTP();
            $mail->Host       = (string)$cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = (string)$cfg['username'];
            $mail->Password   = (string)$cfg['password'];
            $mail->SMTPSecure = (string)$cfg['secure'];
            $mail->Port       = (int)$cfg['port'];

            // Header
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);

            // Technischer From (eigene Domain); echte Absenderadresse als Reply-To
            $mail->setFrom((string)$cfg['from'], (string)$cfg['fromName']);
            if ($from !== '') {
                $mail->addReplyTo($from);
            }

            // Empfänger
            $mail->addAddress((string)$cfg['to']);

            // Betreff
            $mail->Subject = (string)$cfg['subject'];

            // Body
            $intro  = "Neue Nachricht über das Kontaktformular:\n";
            $fromLn = $from !== '' ? "Absender: {$from}\n" : "Absender: (keine gültige E-Mail angegeben)\n";
            $sep    = str_repeat('-', 40) . "\n";
            $mail->Body = $intro . $fromLn . $sep . $text . "\n";

            // Senden
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Fehler nur loggen (kein Throw)
            \error_log('[ContactService] Mail send failed: ' . $e->getMessage());
            return false;
        }
    }
}
