<?php
declare(strict_types=1);

// Zweck: Admin-Controller zum Listen, Erstellen, Bearbeiten, Veröffentlichen und Löschen von Posts mit Zugriffsprüfung, Validierung und Bild-Upload.

namespace App\Http\Controllers\Admin;

use App\Infrastructure\Repositories\DbRepository;

class AdminPostController
{
    // Repo via Konstruktor-Injection
    public function __construct(private DbRepository $db) {}

    // Übersicht: Posts + User-Mapping für Anzeige (author)
    public function index(): array
    {
        // Posts nach Datum absteigend
        $posts = $this->db->selectAll('posts', [], 'created_at DESC');
        // Alle Nutzer laden, um ID → Username abzubilden
        $users = $this->db->selectAll('users', [], 'id ASC');
        $usersById = [];
        foreach ($users as $u) $usersById[(int)$u['id']] = (string)$u['username'];
        // Kompaktes View-Model
        return compact('posts', 'usersById');
    }

    // Löschen eines Posts (Owner oder Admin)
    public function delete(int $id): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly(); // nur eingeloggte Nutzer

        try {
            // Post existiert?
            $post = $this->db->selectOne('posts', ['id' => $id]);
            if (!$post) {
                $_SESSION['message'] = 'Post nicht gefunden.'; $_SESSION['type'] = 'error';
                $this->backToIndex();
            }
            // Rechte prüfen: Owner oder Admin
            $isOwner = ((int)$post['user_id'] === (int)($_SESSION['id'] ?? 0));
            $isAdmin = !empty($_SESSION['admin']);
            if (!$isAdmin && !$isOwner) {
                $_SESSION['message'] = 'Nicht erlaubt.'; $_SESSION['type'] = 'error';
                $this->backToIndex();
            }

            // Löschen
            $rows = $this->db->delete('posts', $id);
            $_SESSION['message'] = $rows > 0 ? 'Post wurde gelöscht.' : 'Post konnte nicht gelöscht werden.';
            $_SESSION['type']    = $rows > 0 ? 'success' : 'error';
        } catch (\Throwable $e) {
            // Fehlerfall
            $_SESSION['message'] = 'Löschen fehlgeschlagen: ' . $e->getMessage();
            $_SESSION['type']    = 'error';
        }
        $this->backToIndex();
    }

    // Publish-Status setzen (nur Admin)
    public function togglePublish(int $postId, int $published): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly();
        if (empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nicht erlaubt.'; $_SESSION['type'] = 'error';
            $this->backToIndex();
        }

        // Post prüfen
        $post = $this->db->selectOne('posts', ['id' => $postId]);
        if (!$post) {
            $_SESSION['message'] = 'Post nicht gefunden.'; $_SESSION['type'] = 'error';
            $this->backToIndex();
        }

        // Status aktualisieren
        $this->db->update('posts', $postId, ['published' => $published ? 1 : 0]);
        $_SESSION['message'] = 'Publish-Status geändert.'; $_SESSION['type'] = 'success';
        $this->backToIndex();
    }

    // Erstellen-Verarbeitung (Formular-POST)
    public function handleCreate(array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/Support/helpers/validatePost.php';

        // Topics für Select
        $topics = $this->db->selectAll('topics', [], 'name ASC');
        $vmBase = ['errors'=>[], 'title'=>'', 'body'=>'', 'topic_id'=>'', 'published'=>'', 'topics'=>$topics];

        // Noch nicht gesendet → nur leeres VM zurück
        if (!isset($post['add-post'])) return $vmBase;

        // Validieren
        $errors    = validatePost($post, $files);
        // Bild hochladen (required=true)
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, true);

        // Fehler → Formwerte zurückspiegeln
        if ($errors) {
            return [
                'errors'    => $errors,
                'title'     => $post['title']     ?? '',
                'body'      => $post['body']      ?? '',
                'topic_id'  => $post['topic_id']  ?? '',
                'published' => !empty($post['published']) ? 1 : 0,
                'topics'    => $topics,
                'image'     => '', // neu erstellen → kein vorhandenes Bild
            ];
        }

        // Session sicherstellen
        if (session_status() === \PHP_SESSION_NONE) session_start();

        // Insert-Daten vorbereiten
        $data = [
            'title'    => (string)$post['title'],
            'body'     => (string)$post['body'],
            'topic_id' => (int)$post['topic_id'],
            'user_id'  => (int)($_SESSION['id'] ?? 0),
            'image'    => $imageName,
        ];

        // Admin darf direkt publishen, Nutzer nur einreichen
        if (!empty($_SESSION['admin'])) {
            $data['published'] = !empty($post['published']) ? 1 : 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post erstellt.';
        } else {
            if (!empty($post['AdminPublish'])) $this->sendPublishEmail($post['title']);
            $data['published'] = 0;
            $this->db->create('posts', $data);
            $_SESSION['message'] = 'Post eingereicht (wartet auf Freigabe).';
        }
        $_SESSION['type'] = 'success';
        $this->backToIndex();
        return [];
    }

    // Bearbeiten-Verarbeitung: Laden (GET id) oder Update (POST)
    public function handleEdit(array $get, array $post, array $files): array
    {
        require_once ROOT_PATH . '/app/Support/helpers/validatePost.php';
        if (session_status() === \PHP_SESSION_NONE) session_start();
        usersOnly();

        // Topics für Select
        $topics = $this->db->selectAll('topics', [], 'name ASC');

        // GET id → Post laden und für Formular zurückgeben
        if (isset($get['id'])) {
            $p = $this->db->selectOne('posts', ['id' => (int)$get['id']]);
            if (!$p) {
                return ['errors'=>['Post nicht gefunden.'],'id'=>0,'title'=>'','body'=>'','topic_id'=>'','published'=>'','topics'=>$topics,'image'=>''];
            }
            $isOwner = ((int)$p['user_id'] === (int)($_SESSION['id'] ?? 0));
            $isAdmin = !empty($_SESSION['admin']);
            if (!$isAdmin && !$isOwner) {
                return ['errors'=>['Nicht erlaubt.'],'id'=>0,'title'=>'','body'=>'','topic_id'=>'','published'=>'','topics'=>$topics,'image'=>''];
            }
            // Erfolgsfall: Werte fürs Formular (Body decodieren)
            return [
                'errors'    => [],
                'id'        => (int)$p['id'],
                'title'     => (string)$p['title'],
                'body'      => html_entity_decode((string)$p['body'], ENT_QUOTES, 'UTF-8'),
                'topic_id'  => (int)$p['topic_id'],
                'published' => (int)$p['published'],
                'topics'    => $topics,
                'image'     => (string)($p['image'] ?? ''),  // aktuelles Bild
            ];
        }

        // Kein Update-Submit → leeres VM
        if (!isset($post['update-post'])) {
            return ['errors'=>[], 'id'=>'', 'title'=>'', 'body'=>'', 'topic_id'=>'', 'published'=>'', 'topics'=>$topics, 'image'=>''];
        }

        // Original laden und Rechte prüfen
        $id   = (int)$post['id'];
        $orig = $this->db->selectOne('posts', ['id' => $id]);
        if (!$orig) {
            return ['errors'=>['Post nicht gefunden.'],'id'=>0,'title'=>'','body'=>'','topic_id'=>'','published'=>'','topics'=>$topics,'image'=>''];
        }

        $isOwner = ((int)$orig['user_id'] === (int)$_SESSION['id']);
        $isAdmin = !empty($_SESSION['admin']);
        if (!$isAdmin && !$isOwner) {
            return ['errors'=>['Nicht erlaubt.'],'id'=>0,'title'=>'','body'=>'','topic_id'=>'','published'=>'','topics'=>$topics,'image'=>''];
        }

        // Validieren & optional Bild hochladen (required=false)
        $errors    = validatePost($post, $files);
        $imageName = $this->uploadImage($files['image'] ?? null, $errors, false);

        // Fehler → Werte zurückgeben (aktuelles/übergebenes Bild erhalten)
        if ($errors) {
            return [
                'errors'    => $errors,
                'id'        => $id,
                'title'     => $post['title']     ?? '',
                'body'      => $post['body']      ?? '',
                'topic_id'  => $post['topic_id']  ?? '',
                'published' => !empty($post['published']) ? 1 : 0,
                'topics'    => $topics,
                'image'     => (string)($post['current_image'] ?? $orig['image'] ?? ''), 
            ];
        }

        // Felder vorbereiten (Body RAW speichern)
        $data = [
            'title'    => (string)$post['title'],
            'body'     => (string)$post['body'],
            'topic_id' => (int)$post['topic_id'],
        ];

        // Bild ersetzen oder beibehalten
        $data['image'] = $imageName !== null
            ? $imageName
            : (string)($post['current_image'] ?? $orig['image'] ?? '');

        // published nur Admin änderbar
        if ($isAdmin) {
            $data['published'] = !empty($post['published']) ? 1 : 0;
        } else {
            $data['published'] = (int)$orig['published'];
        }

        // Update ausführen
        $this->db->update('posts', $id, $data);

        // Erfolg & zurück zur Übersicht
        $_SESSION['message'] = 'Post gespeichert.';
        $_SESSION['type']    = 'success';
        $this->backToIndex();
        return [];
    }

    // Bild-Upload (einfacher Move; kompatibel zum Legacy-Verhalten)
    private function uploadImage(?array $file, array &$errors, bool $required = true): ?string
    {
        if (!$file || empty($file['name'])) {
            if ($required) $errors[] = 'Post image required';
            return null;
        }
        // Dateiname mit Timestamp
        $imageName = time() . '_' . basename((string)$file['name']);
        $dest      = ROOT_PATH . '/public/resources/assets/images/' . $imageName;

        // Upload durchführen
        if (!@move_uploaded_file($file['tmp_name'] ?? '', $dest)) {
            $errors[] = 'Failed to upload image';
            return null;
        }
        return $imageName;
    }

    // Optionale E-Mail an Admin (Freigabe-Hinweis)
    private function sendPublishEmail(string $title): void
    {
        $information = ['Title' => $title, 'nachricht' => 'Bitte veröffentlichen Sie diesen Post'];
        require ROOT_PATH . '/app/Support/helpers/send-email.php';
    }

    // Zur passenden Übersicht zurück (Admin-Index oder User-Dashboard)
    private function backToIndex(): void
    {
        if (session_status() === \PHP_SESSION_NONE) session_start();

        $isAdmin = !empty($_SESSION['admin']);

        $adminUrl     = BASE_URL . '/public/admin/posts/index.php';
        $dashboardUrl = BASE_URL . '/public/users/dashboard.php';   

        $target = $isAdmin ? $adminUrl : $dashboardUrl;

        header('Location: ' . $target);
        exit;
    }
}
