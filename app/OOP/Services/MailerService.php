<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * MailerService
 *
 * Versendet einfache Text-Mails über die native mail()-Funktion.
 * Fällt bei Nichtverfügbarkeit auf error_log() zurück (kein Exception-Throw),
 * um den Prozess nicht zu unterbrechen – wie im Legacy-Verhalten.
 */
class MailerService
{
    /**
     * Sendet eine einfache Text-Mail.
     * - $to, $subject optional (Default: MAIL_TO bzw. admin@example.com; "Blog Benachrichtigung")
     * - $payload wird zu einem Textkörper formatiert (Key: Value je Zeile)
     *
     * @param array       $payload Beliebige Schlüssel/Werte, z. B. ['Title' => '...', 'nachricht' => '...']
     * @param string|null $to      Empfängeradresse (optional)
     * @param string|null $subject Betreff (optional)
     *
     * @return bool true bei Erfolg oder beim Fallback-Logging, false wird nicht verwendet (stilles Verhalten)
     */
    public static function send(array $payload = [], ?string $to = null, ?string $subject = null): bool
    {
        $to      = $to      ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
        $subject = $subject ?? 'Blog Benachrichtigung';
        $body    = self::formatBody($payload);

        // Versuche native mail()
        $headers = 'Content-Type: text/plain; charset=UTF-8';
        $ok      = @mail($to, $subject, $body, $headers);

        if (!$ok) {
            // Fallback: stilles Logging statt Abbruch/Exception
            error_log("[MailerService] To: {$to} | Subj: {$subject} | Body:\n{$body}");
            return true;
        }

        return true;
    }

    /**
     * Formatiert den Body aus einem Payload-Array.
     * - Jede Zeile: "Key: Value"
     * - Leerer Payload → Platzhalterzeile
     */
    private static function formatBody(array $payload): string
    {
        if ($payload === []) {
            return 'Kein Inhalt übermittelt.';
        }

        $lines = [];
        foreach ($payload as $k => $v) {
            $lines[] = "{$k}: {$v}";
        }

        return implode("\n", $lines);
    }
}
