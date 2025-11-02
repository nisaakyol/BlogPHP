<?php
declare(strict_types=1);

// Zweck: Nimmt Kommentare entgegen, validiert sie (CSRF, Honeypot, reCAPTCHA, Rate-Limit), speichert sie und liefert optional den Kommentarbaum.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\DbRepository;

final class CommentController
{
    // Repository per Konstruktor-Injection
    public function __construct(private DbRepository $repo) {}

    // Speichert einen Kommentar und leitet zurück auf den Post
    public function store(array $data): void
    {
        // Nur POST zulassen
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        // Session sicherstellen
        if (session_status() === PHP_SESSION_NONE) session_start();

        // CSRF-Helfer laden
        require_once ROOT_PATH . '/app/Support/helpers/csrf.php';

        // Log für Debugging (Tokenvergleich)
        error_log(
            'CSRF_POST sid=' . session_id()
            . ' sessTok=' . ($_SESSION['csrf']['token'] ?? 'NULL')
            . ' postTok=' . ($data['csrf_token'] ?? 'NULL')
        );

        // CSRF prüfen
        if (!csrf_validate($data['csrf_token'] ?? null)) {
            $_SESSION['message'] = 'Dein Formular ist abgelaufen oder ungültig. Bitte erneut versuchen.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/single.php?id=' . (int)($data['post_id'] ?? 0) . '#comments');
            exit;
        }

        // Honeypot (Bots aussortieren)
        $hp = trim((string)($data['hp_name'] ?? ''));
        if ($hp !== '') { http_response_code(204); exit; }

        // Optional: reCAPTCHA v3 prüfen (wenn Secret gesetzt)
        $rcSecret = getenv('RECAPTCHA_V3_SECRET') ?: getenv('RECAPTCHA_SECRET') ?: '';
        $rcToken  = (string)($data['g-recaptcha-response'] ?? '');
        $rcAction = (string)($data['recaptcha_action'] ?? 'submit_comment');
        $rcMin    = (float)(getenv('RECAPTCHA_MIN_SCORE') ?: 0.5);

        if ($rcSecret !== '' && $rcToken !== '') {
            // Anfrage an Google vorbereiten
            $postFields = http_build_query([
                'secret'   => $rcSecret,
                'response' => $rcToken,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                               . "Content-Length: " . strlen($postFields) . "\r\n",
                    'content' => $postFields,
                    'timeout' => 6,
                ],
            ]);
            $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);

            // Antwort validieren
            $ok = false;
            if ($raw !== false) {
                $res      = json_decode($raw, true);
                $actionOk = empty($res['action']) || $res['action'] === $rcAction;
                $score    = (float)($res['score'] ?? 0.0);
                $ok       = !empty($res['success']) && $actionOk && ($score >= $rcMin);
            }

            // Fehlermeldung bei nicht bestandenem Check
            if (!$ok) {
                $_SESSION['message'] = 'Sicherheitsprüfung fehlgeschlagen. Bitte versuch es erneut.';
                $_SESSION['type']    = 'error';
                header('Location: ' . BASE_URL . '/public/single.php?id=' . (int)($data['post_id'] ?? 0) . '#comments');
                exit;
            }
        }

        // Eingaben lesen
        $postId   = (int)($data['post_id'] ?? 0);
        $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
        $comment  = trim((string)($data['comment'] ?? ''));

        // Basisvalidierung
        $errors = [];
        if ($postId <= 0)                               $errors[] = 'Ungültiger Beitrag.';
        if ($comment === '' || mb_strlen($comment) < 3) $errors[] = 'Kommentar ist zu kurz.';

        // Autor ermitteln (eingeloggt oder Gast)
        $isLoggedIn = !empty($_SESSION['id']);
        $username   = $isLoggedIn ? (string)($_SESSION['username'] ?? 'User')
                                  : trim((string)($data['author_name'] ?? ''));

        // Gast muss Namen angeben
        if (!$isLoggedIn && ($username === '' || mb_strlen($username) < 2)) {
            $errors[] = 'Bitte gib einen Namen an.';
        }

        // Einfaches Rate-Limit
        $now  = time();
        $last = (int)($_SESSION['last_comment_ts'] ?? 0);
        if ($now - $last < 20) $errors[] = 'Bitte warte kurz vor dem nächsten Kommentar.';
        $_SESSION['last_comment_ts'] = $now;

        // Bei Fehlern zurück
        if ($errors) {
            $_SESSION['message'] = implode(' ', $errors);
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/single.php?id=' . max(0, $postId) . '#comments');
            exit;
        }

        // Speichern je nach Login-Status
        if ($isLoggedIn) {
            $userId = (int)$_SESSION['id'];
            $id     = $this->repo->createComment($postId, $userId, $comment, $parentId);
        } else {
            // Fallback, wenn Gast-Methode fehlt
            if (!method_exists($this->repo, 'createCommentGuest')) {
                throw new \RuntimeException('Repo-Methode createCommentGuest fehlt.');
            }
            $id = $this->repo->createCommentGuest($postId, $username, $comment, $parentId);

            // Autorname per Cookie merken (optional)
            require_once ROOT_PATH . '/app/Support/helpers/cookies.php';
            if (!empty($data['remember_author'])) {
                $payload = json_encode(['name' => $username], JSON_UNESCAPED_UNICODE);
                set_cookie_safe('comment_author', $payload, 60*60*24*180);
            } else {
                set_cookie_safe('comment_author', '', -3600);
            }
        }

        // Erfolgsmeldung und Anker auf den neuen Kommentar
        $_SESSION['message'] = $isLoggedIn ? 'Kommentar gespeichert.' : 'Danke! Dein Kommentar ist sichtbar.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/single.php?id=' . $postId . '#comment-' . (int)$id);
        exit;
    }

    // Optional: Kommentarbaum für einen Post erzeugen (verschachtelte Struktur)
    public function treeForPost(int $postId): array
    {
        // Alle Kommentare laden
        $all = $this->repo->fetchCommentsForPost($postId);
        if (!$all) return [];

        // Nach parent_id gruppieren
        $byParent = [];
        foreach ($all as $row) {
            $pid = isset($row['parent_id']) && $row['parent_id'] !== null ? (int)$row['parent_id'] : 0;
            $row['id'] = (int)$row['id'];
            $byParent[$pid][] = $row;
        }

        // Rekursiv Children aufbauen
        $build = function (int $parentId) use (&$build, &$byParent): array {
            $children = $byParent[$parentId] ?? [];
            foreach ($children as &$c) {
                $c['children'] = $build($c['id']);
            }
            return $children;
        };

        // Wurzel (parent_id = 0) zurückgeben
        return $build(0);
    }
}
