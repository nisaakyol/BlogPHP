<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

/**
 * AdminPostController
 *
 * Zuständig für:
 * - Listenansicht (index)
 * - Löschen (delete)
 * - Publish/Unpublish (togglePublish)
 * - Erstellen (handleCreate)
 * - Bearbeiten (handleEdit)
 *
 * Hinweise:
 * - Validierung erfolgt über die Helper (validatePost.php).
 * - Bild-Uploads werden in /assets/images/ abgelegt.
 * - Nach write-Aktionen wird per Header-Redirect zur Übersicht navigiert.
 */
class AdminPostController
{
    public function __construct(private DbRepository $db)
    {
    }

    /* ---------- INDEX ---------- */

    /**
     * Liefert Posts (absteigend nach created_at) und eine User-Map (id => username)
     *
     * @return array{posts: array, usersById: array<int, string>}
     */
    public function index(): array
    {
        $posts = $this->db->selectAll('posts', [], 'created_at DESC');

        // User-Map (id => username) für die Anzeige
        $users     = $this->db->selectAll('users', [], 'id ASC');
        $usersById = [];
        foreach ($users as $u) {
            $usersById[(int) $u['id']] = (string) $u['username'];
        }

        return compact('posts', 'usersById');
    }

    /* ---------- DELETE via GET ?delete_id=ID ---------- */

    /**
     * Löscht einen Post und zeigt eine Flash-Message.
     */
    public function delete(int $id): void
    {
        $this->db->delete('posts', $id);

        $_SESSION['message'] = 'Post wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit;
    }

    /* ---------- Publish Toggle: ?published=0/1&p_id=ID ---------- */

    /**
     * Setzt den Published-Status eines Posts (0/1) und zeigt eine Flash-Message.
     */
    public function togglePublish(int $postId, int $published): void
    {
        $this->db->update('posts', $postId, ['published' => $published]);

        $_SESSION['message'] = 'Post published Status geändert!';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit;
    }

    /* ---------- CREATE (GET + POST) ---------- */

    /**
     * Handhabt das Erstellen eines neuen Posts.
     * - GET: liefert Defaults + Topics
     * - POST (name="add-post"): validiert, lädt ggf. Bild hoch und speichert
     *
     * @param array $post  typ. $_POST
     * @param array $files typ. $_FILES
     *
     * @return array ViewModel für die Formular-View
     */
    public function handleCreate(array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/helpers/validatePost.php';

        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // Defaults für Formular
        $vm = [
            'errors'    => [],
            'title'     => '',
            'body'      => '',
            'topic_id'  => '',
            'published' => '',
            'topics'    => $topics,
        ];

        // Initialer GET-Aufruf → VM mit Defaults zurückgeben
        if (!isset($post['add-post'])) {
            return $vm;
        }

        // Validierung
        $errors    = validatePost($post);
        $imageName = $this->uploadImage($files['image'] ?? null, $errors);

        // Fehler → Formular mit alten Werten wieder befüllen
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

        // Persistenzdaten
        $data = [
            'title'    => $post['title'],
            'body'     => htmlentities($post['body']), // Legacy: HTML speichern als Entities
            'topic_id' => (int) $post['topic_id'],
            'user_id'  => (int) $_SESSION['id'],
            'image'    => $imageName,
        ];

        if (!empty($_SESSION['admin'])) {
            // Admin darf direkt veröffentlichen
            $data['published'] = !empty($post['published']) ? 1 : 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post created successfuly';
        } else {
            // Normaler User: optional Freigabe-Mail an Admin
            if (!empty($post['AdminPublish'])) {
                $this->sendPublishEmail($post['title']);
            }
            $data['published'] = 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post wurde zur Veröffentlichung versandt';
        }

        $_SESSION['type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit;
    }

    /* ---------- EDIT (GET + POST) ---------- */

    /**
     * Handhabt das Bearbeiten eines bestehenden Posts.
     * - GET ?id=…   : lädt Post und Topics für das Formular
     * - POST update : validiert, optional Bild hochladen/ersetzen, speichert
     *
     * @param array $get   typ. $_GET
     * @param array $post  typ. $_POST
     * @param array $files typ. $_FILES
     *
     * @return array ViewModel für die Formular-View
     */
    public function handleEdit(array $get, array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/helpers/validatePost.php';

        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // GET ?id => Formular mit Daten befüllen
        if (isset($get['id'])) {
            $p = $this->db->selectOne('posts', ['id' => (int) $get['id']]);

            return [
                'errors'    => [],
                'id'        => $p['id'],
                'title'     => $p['title'],
                'body'      => $p['body'],
                'topic_id'  => $p['topic_id'],
                'published' => (int) $p['published'],
                'topics'    => $topics,
            ];
        }

        // Kein POST-Submit → leere VM (mit Topics) zurückgeben
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

        // Validierung
        $errors    = validatePost($post);
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, false); // Bild optional

        // Fehler → Formular mit alten Werten wieder befüllen
        if ($errors) {
            return [
                'errors'    => $errors,
                'id'        => (int) ($post['id'] ?? 0),
                'title'     => $post['title']     ?? '',
                'body'      => $post['body']      ?? '',
                'topic_id'  => $post['topic_id']  ?? '',
                'published' => !empty($post['published']) ? 1 : 0,
                'topics'    => $topics,
            ];
        }

        $id = (int) $post['id'];

        $data = [
            'title'     => $post['title'],
            'body'      => htmlentities($post['body']), // Legacy: HTML speichern als Entities
            'topic_id'  => (int) $post['topic_id'],
            'user_id'   => (int) $_SESSION['id'],
            'published' => !empty($post['published']) ? 1 : 0,
        ];

        // Neues Bild übernehmen, wenn vorhanden
        if ($imageName) {
            $data['image'] = $imageName;
        }

        $this->db->update('posts', $id, $data);

        $_SESSION['message'] = 'Post update erfolgreich';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit;
    }

    /* ---------- Helpers ---------- */

    /**
     * Bild-Upload.
     *
     * @param array|null $file     $_FILES['image']-Struktur oder null
     * @param array      $errors   Referenz auf Fehlerliste (wird befüllt)
     * @param bool       $required true = Bild ist Pflicht, false = optional
     *
     * @return string|null  Dateiname bei Erfolg, sonst null
     */
    private function uploadImage(?array $file, array &$errors, bool $required = true): ?string
    {
        if (!$file || empty($file['name'])) {
            if ($required) {
                $errors[] = 'Post image required';
            }
            return null;
        }

        // Zielname: Zeitstempel + Original-Basename
        $imageName = time() . '_' . basename($file['name']);
        $dest      = ROOT_PATH . '/assets/images/' . $imageName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = 'Failed to upload image';
            return null;
        }

        return $imageName;
    }

    /**
     * Versendet eine Info-Mail an den Admin für Veröffentlichungsfreigabe.
     */
    private function sendPublishEmail(string $title): void
    {
        // Gleiche Logik wie früher (sammelstelledhbwblog@gmail.com)
        $information = [
            'Title'     => $title,
            'nachricht' => 'Bitte veröffentlichen Sie diesen Post',
        ];

        require ROOT_PATH . '/app/helpers/send-email.php';
    }
}
