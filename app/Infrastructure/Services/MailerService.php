<?php
declare(strict_types=1);

// Zweck: Versand von Benachrichtigungs-Mails (PHPMailer bevorzugt, sonst mail(); HTML/Alt, Reply-To, weiche Fallbacks)

namespace App\Infrastructure\Services;

class MailerService
{
    /**
     * Sendet eine einfache Text-/HTML-Mail aus einem Key/Value-Payload.
     *
     * @param array       $payload Beliebige Schlüssel/Werte
     * @param string|null $to      Empfänger
     * @param string|null $subject Betreff
     */
    public static function send(array $payload = [], ?string $to = null, ?string $subject = null): bool
    {
        // Basis-Absender/Empfänger/Betreff auflösen
        $to       = $to ?? (defined('MAIL_TO') ? MAIL_TO : ((getenv('MAIL_TO') !== false) ? (string)getenv('MAIL_TO') : 'admin@example.com'));
        $subject  = $subject ?? 'Blog Benachrichtigung';
        $from     = defined('MAIL_FROM') ? MAIL_FROM : ((getenv('MAIL_FROM') !== false) ? (string)getenv('MAIL_FROM') : 'no-reply@example.com');
        $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : ((getenv('MAIL_FROM_NAME') !== false) ? (string)getenv('MAIL_FROM_NAME') : 'Blog');

        // Optionales Reply-To aus Payload
        $replyTo = null;
        if (isset($payload['from_email']) && filter_var((string)$payload['from_email'], FILTER_VALIDATE_EMAIL)) {
            $replyTo = (string)$payload['from_email'];
        }

        // Body (HTML/ALT) bestimmen
        $hasHtml = isset($payload['html']) && is_string($payload['html']) && $payload['html'] !== '';
        $html    = $hasHtml ? (string)$payload['html'] : '';
        $alt     = isset($payload['alt']) && is_string($payload['alt']) ? (string)$payload['alt'] : '';
        if ($alt === '') {
            $alt = self::formatBody($payload);
        }
        $bodyIsHtml = $hasHtml;
        $body       = $bodyIsHtml ? $html : $alt;

        // 1) PHPMailer (wenn vorhanden)
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

                if ($replyTo) {
                    $mail->addReplyTo($replyTo);
                }

                if ($bodyIsHtml) {
                    $mail->isHTML(true);
                    $mail->Body    = $html;
                    $mail->AltBody = $alt !== '' ? $alt : strip_tags($html);
                } else {
                    $mail->isHTML(false);
                    $mail->Body    = $alt;
                    $mail->AltBody = $alt;
                }

                $mail->send();
                return true;
            } catch (\Throwable $e) {
                // weicher Fallback
            }
        }

        // 2) Fallback: native mail()
        $headers = 'From: ' . self::encodeHeader($fromName) . " <{$from}>\r\n"
                 . 'Content-Type: ' . ($bodyIsHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8';
        if ($replyTo) {
            $headers .= "\r\nReply-To: " . $replyTo;
        }

        $ok = @mail($to, self::encodeHeader($subject), $body, $headers);
        if ($ok) {
            return true;
        }

        // 3) Fallback: Logging (kein harter Fehler)
        error_log("[MailerService] SEND FAIL -> To: {$to} | Subj: {$subject}\n{$body}");
        return true;
    }

    // Komfort: Benachrichtigung „Post zur Freigabe“
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

    // Payload → Plaintext
    private static function formatBody(array $payload): string
    {
        if ($payload === []) {
            return 'Kein Inhalt übermittelt.';
        }
        $lines = [];
        foreach ($payload as $k => $v) {
            if (is_array($v) || is_object($v)) { continue; }
            $val = (string)$v;
            if ($val === '') { continue; }
            $lines[] = sprintf('%s: %s', (string)$k, $val);
        }
        return implode("\n", $lines);
    }

    // UTF-8 Header encoden
    private static function encodeHeader(string $text): string
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}
