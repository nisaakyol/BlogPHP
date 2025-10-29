<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

class AdminPostController
{
    public function __construct(private DbRepository $db)
    {
    }

    /* =========================================================================
     * LISTE
     * ========================================================================= */
    /**
     * Liefert Posts (absteigend) + Map der Usernamen.
     * @return array{posts: array<int, array>, usersById: array<int, string>}
     */
    public function index(): array
    {
        $posts = $this->db->selectAll('posts', [], 'created_at DESC');

        $users     = $this->db->selectAll('users', [], 'id ASC');
        $usersById = [];
        foreach ($users as $u) {
            $usersById[(int)$u['id']] = (string)$u['username'];
        }

        return compact('posts', 'usersById');
    }

    /* =========================================================================
     * LÖSCHEN (?delete_id=ID)
     * ========================================================================= */
    public function delete(int $id): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly();

        try {
            // Post holen (für Exists-/Rechte-Check)
            $post = $this->db->selectOne('posts', ['id' => $id]);
            if (!$post) {
                $_SESSION['message'] = 'Post nicht gefunden.';
                $_SESSION['type']    = 'error';
                $this->backToIndex();
            }

            $isOwner = ((int)$post['user_id'] === (int)($_SESSION['id'] ?? 0));
            $isAdmin = !empty($_SESSION['admin']);
            if (!$isAdmin && !$isOwner) {
                $_SESSION['message'] = 'Nicht erlaubt.';
                $_SESSION['type']    = 'error';
                $this->backToIndex();
            }

            // Optional: weitere Tabellen bereinigen …
            $rows = $this->db->delete('posts', $id);

            if ($rows > 0) {
                $_SESSION['message'] = 'Post wurde gelöscht.';
                $_SESSION['type']    = 'success';
            } else {
                $_SESSION['message'] = 'Post konnte nicht gelöscht werden.';
                $_SESSION['type']    = 'error';
            }
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Löschen fehlgeschlagen: ' . $e->getMessage();
            $_SESSION['type']    = 'error';
        }

        $this->backToIndex();
    }

    /* =========================================================================
     * PUBLISH TOGGLE (?published=0/1&p_id=ID)
     * ========================================================================= */
    public function togglePublish(int $postId, int $published): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly();

        // NEU: Nur Admin darf publish/unpublish
        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nicht erlaubt.';
            $_SESSION['type']    = 'error';
            $this->backToIndex();
        }

        // Existenzcheck (optional sinnvoll)
        $post = $this->db->selectOne('posts', ['id' => $postId]);
        if (!$post) {
            $_SESSION['message'] = 'Post nicht gefunden.';
            $_SESSION['type']    = 'error';
            $this->backToIndex();
        }

        $this->db->update('posts', $postId, ['published' => $published ? 1 : 0]);

        $_SESSION['message'] = 'Publish-Status geändert.';
        $_SESSION['type']    = 'success';

        $this->backToIndex();
    }

    /* =========================================================================
     * CREATE (GET + POST)
     * ========================================================================= */
    /**
     * @param array $post  i.d.R. $_POST
     * @param array $files i.d.R. $_FILES
     * @return array ViewModel
     */
    public function handleCreate(array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/helpers/validatePost.php';

        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // Initiales ViewModel (GET)
        $vm = [
            'errors'    => [],
            'title'     => '',
            'body'      => '',
            'topic_id'  => '',
            'published' => '',
            'topics'    => $topics,
        ];
        if (!isset($post['add-post'])) {
            return $vm;
        }

        // Validierung
        $errors    = validatePost($post, $files);

        // Upload (bei Create Pflicht)
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, true);

        if ($errors) {
            return [
                'errors'    => $errors,
                'title'     => $post['title']     ?? '',
                'body'      => $post['body']      ?? '',
                'topic_id'  => $post['topic_id']  ?? '',
                'published' => !empty($post['published']) ? 1 : 0,
                'topics'    => $topics,
            ];
        }

        if (session_status() === \PHP_SESSION_NONE) session_start();

        // Speicherdaten (Body RAW speichern)
        $data = [
            'title'    => (string)$post['title'],
            'body'     => (string)$post['body'],     // RAW
            'topic_id' => (int)$post['topic_id'],
            'user_id'  => (int)($_SESSION['id'] ?? 0),
            'image'    => $imageName,
        ];

        if (!empty($_SESSION['admin'])) {
            $data['published'] = !empty($post['published']) ? 1 : 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post erstellt.';
        } else {
            if (!empty($post['AdminPublish'])) {
                $this->sendPublishEmail($post['title']);
            }
            $data['published'] = 0;
            // Optional: $data['status'] = 'submitted';
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post eingereicht (wartet auf Freigabe).';
        }

        $_SESSION['type'] = 'success';
        $this->backToIndex();
        return []; // unerreicht
    }

    /* =========================================================================
     * EDIT (GET + POST)
     * ========================================================================= */
    /**
     * @param array $get   i.d.R. $_GET
     * @param array $post  i.d.R. $_POST
     * @param array $files i.d.R. $_FILES
     * @return array ViewModel
     */
    public function handleEdit(array $get, array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/helpers/validatePost.php';
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly();

        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // GET ?id => Formular befüllen
        if (isset($get['id'])) {
            $p = $this->db->selectOne('posts', ['id' => (int)$get['id']]);
            if (!$p) {
                return [
                    'errors'    => ['Post nicht gefunden.'],
                    'id'        => 0,
                    'title'     => '',
                    'body'      => '',
                    'topic_id'  => '',
                    'published' => '',
                    'topics'    => $topics,
                ];
            }

            // Rechte: Admin oder Besitzer
            $isOwner = ((int)$p['user_id'] === (int)($_SESSION['id'] ?? 0));
            $isAdmin = !empty($_SESSION['admin']);
            if (!$isAdmin && !$isOwner) {
                return [
                    'errors'    => ['Nicht erlaubt.'],
                    'id'        => 0,
                    'title'     => '',
                    'body'      => '',
                    'topic_id'  => '',
                    'published' => '',
                    'topics'    => $topics,
                ];
            }

            return [
                'errors'    => [],
                'id'        => (int)($p['id'] ?? 0),
                'title'     => (string)($p['title'] ?? ''),
                // Für das Formular: Body DEkodieren -> Editor zeigt kein &lt;p&gt; an
                'body'      => html_entity_decode((string)($p['body'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'topic_id'  => (int)($p['topic_id'] ?? 0),
                'published' => (int)($p['published'] ?? 0),
                'topics'    => $topics,
            ];
        }

        // Kein Submit -> leeres VM
        if (!isset($post['update-post'])) {
            return [
                'errors'    => [],
                'id'        => '',
                'title'     => '',
                'body'      => '',
                'topic_id'  => '',
                'published' => '',
                'topics'    => $topics,
            ];
        }

        // Original-Post laden (Ownership & aktueller published-Status)
        $id   = (int)$post['id'];
        $orig = $this->db->selectOne('posts', ['id' => $id]);
        if (!$orig) {
            return [
                'errors'    => ['Post nicht gefunden.'],
                'id'        => 0,
                'title'     => '',
                'body'      => '',
                'topic_id'  => '',
                'published' => '',
                'topics'    => $topics,
            ];
        }

        // Rechte: Admin oder Besitzer
        $isOwner = ((int)$orig['user_id'] === (int)($_SESSION['id'] ?? 0));
        $isAdmin = !empty($_SESSION['admin']);
        if (!$isAdmin && !$isOwner) {
            return [
                'errors'    => ['Nicht erlaubt.'],
                'id'        => 0,
                'title'     => '',
                'body'      => '',
                'topic_id'  => '',
                'published' => '',
                'topics'    => $topics,
            ];
        }

        // Validierung
        $errors    = validatePost($post, $files);

        // Upload (optional)
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, false);

        if ($errors) {
            return [
                'errors'    => $errors,
                'id'        => $id,
                'title'     => $post['title']     ?? '',
                'body'      => $post['body']      ?? '',
                'topic_id'  => $post['topic_id']  ?? '',
                'published' => !empty($post['published']) ? 1 : 0,
                'topics'    => $topics,
            ];
        }

        // Speicherdaten (Body RAW speichern) — Ownership bleibt wie im Original!
        $data = [
            'title'    => (string)$post['title'],
            'body'     => (string)$post['body'],  // RAW
            'topic_id' => (int)$post['topic_id'],
            // 'user_id' NICHT überschreiben! (vorher wurde es auf Session-ID gesetzt)
        ];

        if ($imageName) {
            $data['image'] = $imageName;
        }

        // published nur Admin; für normale User published unverändert lassen
        if ($isAdmin) {
            $data['published'] = !empty($post['published']) ? 1 : 0;
        } else {
            $data['published'] = (int)$orig['published']; // unverändert
        }

        $this->db->update('posts', $id, $data);

        $_SESSION['message'] = 'Post gespeichert.';
        $_SESSION['type']    = 'success';

        $this->backToIndex();
        return []; // unerreicht
    }

    /* =========================================================================
     * HELPERS
     * ========================================================================= */
    private function uploadImage(?array $file, array &$errors, bool $required = true): ?string
    {
        if (!$file || empty($file['name'])) {
            if ($required) {
                $errors[] = 'Post image required';
            }
            return null;
        }

        $imageName = time() . '_' . basename((string)$file['name']);
        $dest      = ROOT_PATH . '/assets/images/' . $imageName;

        if (!@move_uploaded_file($file['tmp_name'] ?? '', $dest)) {
            $errors[] = 'Failed to upload image';
            return null;
        }

        return $imageName;
    }

    private function sendPublishEmail(string $title): void
    {
        $information = [
            'Title'     => $title,
            'nachricht' => 'Bitte veröffentlichen Sie diesen Post',
        ];
        require ROOT_PATH . '/app/helpers/send-email.php';
    }

    private function backToIndex(): void
    {
        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit;
    }
}
