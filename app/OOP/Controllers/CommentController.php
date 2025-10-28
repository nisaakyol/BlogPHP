<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\DbRepository;

final class CommentController
{
    public function __construct(private DbRepository $repo) {}

    /**
     * Speichert einen Kommentar und leitet zurück auf den Post.
     * Erwartet in $data: post_id, (optional) parent_id, comment
     */
    public function store(array $data): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ───────────────────────────────── CSRF
        require_once ROOT_PATH . '/app/helpers/csrf.php';
        csrf_validate_or_die($data['csrf_token'] ?? '');

        // ───────────────────────────────── Honeypot (Simple Bot-Falle)
        $hp = trim((string)($data['hp_name'] ?? ''));
        if ($hp !== '') { http_response_code(204); exit; }

        // ───────────────────────────────── reCAPTCHA v3 (unsichtbar)
        // Erwartet Hidden-Felder im Formular:
        //   - g-recaptcha-response
        //   - recaptcha_action (z. B. 'submit_comment')
        $rcSecret = getenv('RECAPTCHA_V3_SECRET') ?: getenv('RECAPTCHA_SECRET') ?: '';
        $rcToken  = (string)($data['g-recaptcha-response'] ?? '');
        $rcAction = (string)($data['recaptcha_action'] ?? 'submit_comment');
        $rcMin    = (float)(getenv('RECAPTCHA_MIN_SCORE') ?: 0.5);

        if ($rcSecret !== '' && $rcToken !== '') {
            // per POST verifizieren (empfohlen)
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

            $ok = false;
            if ($raw !== false) {
                $res = json_decode($raw, true);
                // Manche Integrationen setzen kein 'action' zurück → Action nur prüfen, wenn vorhanden
                $actionOk = empty($res['action']) || $res['action'] === $rcAction;
                $score    = (float)($res['score'] ?? 0.0);
                $ok = !empty($res['success']) && $actionOk && ($score >= $rcMin);
            }

            if (!$ok) {
                $_SESSION['message'] = 'Sicherheitsprüfung fehlgeschlagen. Bitte versuch es erneut.';
                $_SESSION['type']    = 'error';
                header('Location: ' . BASE_URL . '/single.php?id=' . (int)($data['post_id'] ?? 0) . '#comments');
                exit;
            }
        }
        // Hinweis: Wenn kein Secret gesetzt ist (DEV), wird reCAPTCHA stillschweigend übersprungen.

        // ───────────────────────────────── Eingaben prüfen
        $postId   = (int)($data['post_id'] ?? 0);
        $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
        $comment  = trim((string)($data['comment'] ?? ''));

        $errors = [];
        if ($postId <= 0)                               $errors[] = 'Ungültiger Beitrag.';
        if ($comment === '' || mb_strlen($comment) < 3) $errors[] = 'Kommentar ist zu kurz.';

        $isLoggedIn = !empty($_SESSION['id']);
        $username   = '';

        if ($isLoggedIn) {
            $username = (string)($_SESSION['username'] ?? 'User');
        } else {
            // Gast: Name Pflicht
            $username = trim((string)($data['author_name'] ?? ''));
            if ($username === '' || mb_strlen($username) < 2) {
                $errors[] = 'Bitte gib einen Namen an.';
            }
        }

        // ───────────────────────────────── einfaches Rate-Limit (pro Session)
        $now  = time();
        $last = (int)($_SESSION['last_comment_ts'] ?? 0);
        if ($now - $last < 20) $errors[] = 'Bitte warte kurz vor dem nächsten Kommentar.';
        $_SESSION['last_comment_ts'] = $now;

        if ($errors) {
            $_SESSION['message'] = implode(' ', $errors);
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/single.php?id=' . max(0, $postId) . '#comments');
            exit;
        }

        // ───────────────────────────────── Speichern
        if ($isLoggedIn) {
            // eingeloggte Nutzer → Repo speichert mit user_id
            if (!method_exists($this->repo, 'createComment')) {
                throw new \RuntimeException(
                    'Repo loaded: ' . get_class($this->repo)
                    . ' | methods: ' . implode(',', get_class_methods($this->repo))
                );
            }
            $userId = (int)$_SESSION['id'];
            $id     = $this->repo->createComment($postId, $userId, $comment, $parentId);

        } else {
            // Gäste → Repo speichert mit Autor-Name (username/name/author Spalte)
            if (!method_exists($this->repo, 'createCommentGuest')) {
                throw new \RuntimeException(
                    'Bitte DbRepository::createCommentGuest($postId, $username, $comment, $parentId) implementieren.'
                );
            }
            $id = $this->repo->createCommentGuest($postId, $username, $comment, $parentId);

            // Name-merken-Cookie
            require_once ROOT_PATH . '/app/helpers/cookies.php';
            if (!empty($data['remember_author'])) {
                $payload = json_encode(['name' => $username], JSON_UNESCAPED_UNICODE);
                set_cookie_safe('comment_author', $payload, 60 * 60 * 24 * 180); // 180 Tage
            } else {
                set_cookie_safe('comment_author', '', -3600);
            }
        }

        $_SESSION['message'] = $isLoggedIn
            ? 'Kommentar gespeichert.'
            : 'Danke! Dein Kommentar ist sichtbar.';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/single.php?id=' . $postId . '#comment-' . (int)$id);
        exit;
    }

    /**
     * Baut einen Kommentarbaum (parent/children) aus allen Kommentaren zu einem Post.
     * Rückgabeformat: Array von Root-Kommentaren, jeder mit ['children'=>[]]
     */
    public function treeForPost(int $postId): array
    {
        $all = $this->repo->fetchCommentsForPost($postId);
        if (!$all) return [];

        $byParent = [];
        foreach ($all as $row) {
            $pid = isset($row['parent_id']) && $row['parent_id'] !== null
                ? (int)$row['parent_id'] : 0;
            $row['id'] = (int)$row['id'];
            $byParent[$pid][] = $row;
        }

        $build = function (int $parentId) use (&$build, &$byParent): array {
            $children = $byParent[$parentId] ?? [];
            foreach ($children as &$c) {
                $c['children'] = $build($c['id']); // Rekursion mit der ID des Kindes
            }
            return $children;
        };

        return $build(0); // Root-Knoten
    }

    // ───────────────────────────────── interne Helfer (derzeit nicht genutzt)
    private function ensureLoggedIn(): void
    {
        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = 'Bitte einloggen, um zu kommentieren.';
            $_SESSION['type']    = 'error';
            $this->redirect(BASE_URL . '/login.php');
        }
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
