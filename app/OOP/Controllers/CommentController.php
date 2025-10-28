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

        // CSRF
        require_once ROOT_PATH . '/app/helpers/csrf.php';
        csrf_validate_or_die($data['csrf_token'] ?? '');

        // Honeypot (Simple Bot-Falle)
        $hp = trim((string)($data['hp_name'] ?? ''));
        if ($hp !== '') { http_response_code(204); exit; }

        $postId   = (int)($data['post_id'] ?? 0);
        $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
        $comment  = trim((string)($data['comment'] ?? ''));

        $errors = [];
        if ($postId <= 0)                      $errors[] = 'Ungültiger Beitrag.';
        if ($comment === '' || mb_strlen($comment) < 3)
                                            $errors[] = 'Kommentar ist zu kurz.';

        $isLoggedIn = !empty($_SESSION['id']);
        $username   = '';

        if ($isLoggedIn) {
            $username = (string)($_SESSION['username'] ?? 'User');
        } else {
            // Gast: Name Pflicht
            $username    = trim((string)($data['author_name'] ?? ''));
            if ($username === '' || mb_strlen($username) < 2)
                $errors[] = 'Bitte gib einen Namen an.';
        }

        // simples Session-Rate-Limit
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

        // Speichern – je nach Login-Status
        if ($isLoggedIn) {
            // Bestehende Logik für eingeloggte (falls createComment bisher userId annimmt)
            $userId = (int)$_SESSION['id'];

            // Safety: wenn Methode fehlt, Debug-Hinweis
            if (!method_exists($this->repo, 'createComment')) {
                throw new \RuntimeException(
                    'Repo loaded: ' . get_class($this->repo)
                    . ' | methods: ' . implode(',', get_class_methods($this->repo))
                );
            }
            $id = $this->repo->createComment($postId, $userId, $comment, $parentId);

        } else {
            // Gäste: in deiner DB gibt es 'username' (kein user_id) → eigene Repo-Methode
            if (method_exists($this->repo, 'createCommentGuest')) {
                $id = $this->repo->createCommentGuest($postId, $username, $comment, $parentId);
            } else {
                // Fallback: Wenn du createComment erweitern würdest (z. B. userId=null, username als 5. Param)
                // dann hier anpassen. Bis dahin: sauberer Hinweis.
                throw new \RuntimeException(
                    'Bitte DbRepository::createCommentGuest($postId, $username, $comment, $parentId) implementieren.'
                );
            }

            // Cookie „merken“
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
     * Baut einen Kommentarbaum (parent/children) aus allen
     * Kommentaren zu einem Post – ohne das Repository zu ändern.
     *
     * Rückgabeformat: Array von Root-Kommentaren, jeder mit ['children'=>[]]
     */
    public function treeForPost(int $postId): array
    {
        // flache Liste aller Kommentare laden
        $all = $this->repo->fetchCommentsForPost($postId);
        if (!$all) return [];

        // nach parent_id gruppieren (NULL/leer → 0 als Root)
        $byParent = [];
        foreach ($all as $row) {
            $pid = isset($row['parent_id']) && $row['parent_id'] !== null
                ? (int)$row['parent_id'] : 0;
            $row['id'] = (int)$row['id']; // sicherstellen
            $byParent[$pid][] = $row;
        }

        // Rekursiver Builder – WICHTIG: mit der **id** des Kindes weiter aufrufen!
        $build = function (int $parentId) use (&$build, &$byParent): array {
            $children = $byParent[$parentId] ?? [];
            foreach ($children as &$c) {
                $c['children'] = $build($c['id']);   // <-- hier lag der Fehler
            }
            return $children;
        };

        // Root-Knoten haben parent_id = 0/NULL
        return $build(0);
    }

    // ─────────────────────────────────────────────────────────────────────
    // interne Helfer
    // ─────────────────────────────────────────────────────────────────────

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
