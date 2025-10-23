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
        if (empty($_SESSION['id'])) {
            $_SESSION['message'] = 'Bitte einloggen, um zu kommentieren.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $postId   = (int)($data['post_id'] ?? 0);
        $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
        $comment  = trim((string)($data['comment'] ?? ''));

        if ($postId <= 0 || $comment === '') {
            $_SESSION['message'] = 'Kommentar darf nicht leer sein.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/single.php?id=' . max(0, $postId));
            exit;
        }

        $userId = (int)$_SESSION['id'];

        // ⬇️ DEBUG: direkt VOR dem Repo-Aufruf einfügen
        if (!method_exists($this->repo, 'createComment')) {
            throw new \RuntimeException(
                'Repo loaded: ' . get_class($this->repo)
                . ' | methods: ' . implode(',', get_class_methods($this->repo))
            );
        }

        // ursprünglicher Aufruf
        $id = $this->repo->createComment($postId, $userId, $comment, $parentId);

        $_SESSION['message'] = 'Kommentar gespeichert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/single.php?id=' . $postId . '#comment-' . $id);
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
