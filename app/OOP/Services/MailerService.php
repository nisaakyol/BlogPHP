<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * MailerService
 *
 * Versand-Pipeline:
 *   1) PHPMailer via SMTP (wenn verfügbar) – ideal für Mailpit (Host "mail", Port 1025)
 *   2) Fallback: native mail()
 *   3) Fallback: error_log()
 *
 * Konfig (optional via Konstanten oder ENV):
 *   MAIL_TO, MAIL_FROM, MAIL_FROM_NAME,
 *   MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS, MAIL_SECURE
 */
class MailerService
{
    /**
     * Sendet eine einfache Text-Mail aus einem Key/Value-Payload.
     *
     * @param array       $payload Beliebige Schlüssel/Werte
     * @param string|null $to      Empfänger
     * @param string|null $subject Betreff
     */
    public static function send(array $payload = [], ?string $to = null, ?string $subject = null): bool
    {
        // sichere Auflösung ohne verschachtelte ?: / ?? ohne Klammern
        $to       = $to ?? (defined('MAIL_TO') ? MAIL_TO : ((getenv('MAIL_TO') !== false) ? (string)getenv('MAIL_TO') : 'admin@example.com'));
        $subject  = $subject ?? 'Blog Benachrichtigung';
        $from     = defined('MAIL_FROM') ? MAIL_FROM : ((getenv('MAIL_FROM') !== false) ? (string)getenv('MAIL_FROM') : 'no-reply@example.com');
        $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : ((getenv('MAIL_FROM_NAME') !== false) ? (string)getenv('MAIL_FROM_NAME') : 'Blog');

        $body = self::formatBody($payload);

        // 1) Versuche PHPMailer (falls via Composer vorhanden)
        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = defined('MAIL_HOST') ? MAIL_HOST : ((getenv('MAIL_HOST') !== false) ? (string)getenv('MAIL_HOST') : 'mail');
                $mail->Port = (int) (defined('MAIL_PORT') ? MAIL_PORT : ((getenv('MAIL_PORT') !== false) ? (int)getenv('MAIL_PORT') : 1025));

                $secure = defined('MAIL_SECURE') ? MAIL_SECURE : ((getenv('MAIL_SECURE') !== false) ? (string)getenv('MAIL_SECURE') : '');
                if ($secure === 'ssl' || $secure === 'tls') {
                    $mail->SMTPSecure = $secure;
                }

                $user = defined('MAIL_USER') ? MAIL_USER : ((getenv('MAIL_USER') !== false) ? (string)getenv('MAIL_USER') : '');
                $pass = defined('MAIL_PASS') ? MAIL_PASS : ((getenv('MAIL_PASS') !== false) ? (string)getenv('MAIL_PASS') : '');

                $mail->SMTPAuth = ($user !== '' || $pass !== '');
                if ($mail->SMTPAuth) {
                    $mail->Username = $user;
                    $mail->Password = $pass;
                }

                $mail->CharSet = 'UTF-8';
                $mail->setFrom($from, $fromName);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $body;
                $mail->isHTML(false);

                $mail->send();
                return true;
            } catch (\Throwable $e) {
                // weicher Fallback
            }
        }

        // 2) Fallback: native mail()
        $headers = 'From: ' . self::encodeHeader($fromName) . " <{$from}>\r\n"
                 . "Content-Type: text/plain; charset=UTF-8";
        $ok = @mail($to, self::encodeHeader($subject), $body, $headers);
        if ($ok) {
            return true;
        }

        // 3) Fallback: Logging (kein harter Fehler)
        error_log("[MailerService] SEND FAIL -> To: {$to} | Subj: {$subject}\n{$body}");
        return true;
    }

    /**
     * Komfort: Benachrichtigung „Post zur Freigabe“.
     */
    public static function sendPublishPost(array $postData, int $authorId, ?string $topicName = null, ?string $to = null): bool
    {
        $preview = trim(strip_tags((string)($postData['body'] ?? '')));
        $preview = mb_substr($preview, 0, 180) . (mb_strlen($preview) > 180 ? '…' : '');

        $payload = [
            'Hinweis'   => 'Neuer Post zur Freigabe eingereicht',
            'Titel'     => (string)($postData['title'] ?? ''),
            'Autor-ID'  => (string)$authorId,
            'Topic'     => (string)($topicName ?? ''),
            'Vorschau'  => $preview,
        ];
        return self::send($payload, $to, 'Post zur Freigabe');
    }

    /** Formatiert Payload zu mehrzeiligem Text. */
    private static function formatBody(array $payload): string
    {
        if ($payload === []) {
            return 'Kein Inhalt übermittelt.';
        }
        $lines = [];
        foreach ($payload as $k => $v) {
            $lines[] = sprintf('%s: %s', (string)$k, (string)$v);
        }
        return implode("\n", $lines);
    }

    /** Sicheres Encoden für Mail-Header (UTF-8). */
    private static function encodeHeader(string $text): string
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}
