<?php
namespace App\OOP\Services;

class MailerService {
    /**
     * Sendet eine einfache Text-Mail. Fällt auf error_log() zurück, falls mail() nicht verfügbar.
     * $to, $subject, $body optional aus Config; $payload kann z.B. ['Title'=>'...', 'nachricht'=>'...'] enthalten.
     */
    public static function send(array $payload = [], ?string $to = null, ?string $subject = null): bool {
        $to = $to ?? (defined('MAIL_TO') ? MAIL_TO : 'admin@example.com');
        $subject = $subject ?? 'Blog Benachrichtigung';
        $body = self::formatBody($payload);

        // Versuche native mail()
        $headers = "Content-Type: text/plain; charset=UTF-8";
        $ok = @mail($to, $subject, $body, $headers);
        if (!$ok) {
            error_log("[MailerService] To: $to | Subj: $subject | Body:\n$body");
            return true; // Verhalten stillegen statt Prozess zu stoppen
        }
        return true;
    }

    private static function formatBody(array $payload): string {
        if (!$payload) return "Kein Inhalt übermittelt.";
        $lines = [];
        foreach ($payload as $k=>$v) { $lines[] = "$k: $v"; }
        return implode("\n", $lines);
    }
}
