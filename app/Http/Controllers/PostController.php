<?php
declare(strict_types=1);

// Zweck: Verwalten von Blog-Posts (Listen, Erstellen, Aktualisieren, Löschen, Publish-Toggle) inkl. Zugriffs- und Upload-Handling für Admins/Benutzer.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\PostRepository;
use App\Infrastructure\Repositories\CommentRepository;
use App\Infrastructure\Services\UploadService;
use App\Infrastructure\Services\EmailService;
use App\Infrastructure\Services\AuthService;
use App\Infrastructure\Services\AccessService;

class PostController
{
    private PostRepository $repo;
    private AccessService  $access;
    private AuthService    $auth;
    private string         $root;

    // Zustand für die Views (legacy-kompatible Public-Properties)
    public array  $errors    = [];
    public string $table     = 'posts';
    public array  $topics    = [];
    public array  $posts     = [];
    public string $id        = '';
    public string $title     = '';
    public string $body      = '';
    public string $topic_id  = '';
    public string $published = '';

    /**
     * @param string $rootPath Basis-Pfad fürs Dateisystem (für Uploads/Emails)
     *
     * Hinweis: Services werden hier direkt instanziiert, um drop-in zu bleiben.
     */
    public function __construct(string $rootPath)
    {
        $this->root   = $rootPath;
        $this->repo   = new PostRepository();
        $this->auth   = new AuthService();

        // AccessService benötigt Repos für Ownership-Checks
        $this->access = new AccessService(
            $this->auth,
            $this->repo,
            new CommentRepository()
        );
    }

    // Initialzustand laden: Topics, sichtbare Posts (Admin alle, User eigene) und ggf. Formular mit Postdaten befüllen
    public function bootInitialState(): void
    {
        $this->topics = $this->repo->topics();

        if ($this->access->isAdmin()) {
            $this->posts = $this->repo->allOrdered();
        } else {
            $uid = $this->access->currentUserId();
            // Erwartet: PostRepository::listByAuthor(int $userId)
            $this->posts = $uid ? $this->repo->listByAuthor($uid) : [];
        }

        if (isset($_GET['id'])) {
            $id   = (int) $_GET['id'];
            $post = $this->repo->findById($id);
            if ($post) {
                // Nur befüllen, wenn der aktuelle User den Post verwalten darf
                if ($this->access->canManagePost($id)) {
                    $this->id        = (string) $post['id'];
                    $this->title     = (string) $post['title'];
                    $this->body      = (string) $post['body'];
                    $this->topic_id  = (string) $post['topic_id'];
                    $this->published = (string) $post['published'];
                } else {
                    $_SESSION['message'] = 'Nicht erlaubt';
                    $_SESSION['type']    = 'error';
                    header('Location: ' . BASE_URL . '/admin/posts/index.php');
                    exit();
                }
            }
        }
    }

    // Löschen per GET (?delete_id=…): Login + Ownership-Check, dann Flash & Redirect
    public function handleDeleteIfRequested(): void
    {
        if (!isset($_GET['delete_id'])) {
            return;
        }

        AccessService::requireUser();

        $id = (int) $_GET['delete_id'];
        if (!$this->access->canManagePost($id)) {
            $_SESSION['message'] = 'Nicht erlaubt';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/posts/index.php');
            exit();
        }

        $this->repo->delete($id);

        $_SESSION['message'] = 'Post wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit();
    }

    // Publish/Unpublish per GET (?published=0/1&p_id=…): nur Admin, dann Flash & Redirect
    public function handlePublishToggleIfRequested(): void
    {
        if (!isset($_GET['published'], $_GET['p_id'])) {
            return;
        }

        AccessService::requireAdmin();

        $published = (int) $_GET['published'];
        $p_id      = (int) $_GET['p_id'];

        $this->repo->setPublished($p_id, $published);

        $_SESSION['message'] = 'Post published Status geändert!';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/posts/index.php');
        exit();
    }

    // Erstellen bei POST (name="add-post"): Login, Validierung, Upload, Freigabe-Workflow und Redirect
    public function handleCreateIfPosted(): void
    {
        if (!isset($_POST['add-post'])) {
            return;
        }

        AccessService::requireUser();
        $this->errors = validatePost($_POST);

        // Bild hochladen – identische Fehlermeldungen wie Legacy
        if (!empty($_FILES['image']['name'])) {
            [$ok, $imageName] = UploadService::moveImage($this->root, $_FILES['image']);
            if ($ok) {
                $_POST['image'] = $imageName;
            } else {
                $this->errors[] = 'Failed to upload image';
            }
        } else {
            $this->errors[] = 'Post image required';
        }

        if (count($this->errors) === 0) {
            unset($_POST['add-post']);

            $_POST['user_id'] = $this->access->currentUserId();
            $_POST['body']    = htmlentities($_POST['body']);

            if ($this->access->isAdmin()) {
                $_POST['published'] = isset($_POST['published']) ? 1 : 0;
                $this->repo->create($_POST);

                $_SESSION['message'] = 'Post created successfuly';
            } else {
                // Optional: Mail an Admin
                $_POST['AdminPublish'] = isset($_POST['AdminPublish']) ? 1 : 0;
                if (!empty($_POST['AdminPublish'])) {
                    EmailService::sendPublish($_POST, $this->root);
                }
                unset($_POST['AdminPublish']);

                // Freigabe-Workflow: User können nicht direkt publishen
                if (!empty($_SESSION['id']) && !empty($_POST['title'])) {
                    if (!empty($_POST['published'])) { unset($_POST['published']); } // User setzt publish nicht
                    $_POST['status']    = 'submitted';
                    $_POST['published'] = 0;
                } else {
                    $_POST['status']    = 'draft';
                    $_POST['published'] = 0;
                }

                $this->repo->create($_POST);

                $_SESSION['message'] = 'Post wurde zur Freigabe eingereicht';
                $_SESSION['type']    = 'success';
            }

            $_SESSION['type'] = 'success';
            header('Location: ' . BASE_URL . '/admin/posts/index.php');
            exit();
        }

        // Fehlerfall → Formularwerte zurückgeben (legacy-kompatibel)
        $this->title     = $_POST['title']     ?? '';
        $this->topic_id  = $_POST['topic_id']  ?? '';
        $this->body      = $_POST['body']      ?? '';
        $this->published = isset($_POST['published']) ? '1' : '0';
    }

    // Aktualisieren bei POST (name="update-post"): Login, Ownership, Validierung, optionaler Upload, Update und Redirect
    public function handleUpdateIfPosted(): void
    {
        if (!isset($_POST['update-post'])) {
            return;
        }

        AccessService::requireUser();
        $this->errors = validatePost($_POST);

        // Permission vor dem Update prüfen
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0 || !$this->access->canManagePost($id)) {
            $_SESSION['message'] = 'Nicht erlaubt';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/posts/index.php');
            exit();
        }

        // Bild optional neu hochladen
        if (!empty($_FILES['image']['name'])) {
            [$ok, $imageName] = UploadService::moveImage($this->root, $_FILES['image']);
            if ($ok) {
                $_POST['image'] = $imageName;
            } else {
                $this->errors[] = 'Fehler beim Upload des Bilds';
            }
        } else {
            $this->errors[] = 'Post Bild benötigt';
        }

        if (count($this->errors) === 0) {
            unset($_POST['update-post']);

            $_POST['user_id']   = $this->access->currentUserId();
            $_POST['published'] = isset($_POST['published']) ? 1 : 0;
            $_POST['body']      = htmlentities($_POST['body']);

            $this->repo->update($id, $_POST);

            $_SESSION['message'] = 'Post update erfolgreich';
            $_SESSION['type']    = 'success';

            header('Location: ' . BASE_URL . '/admin/posts/index.php');
            exit();
        }

        // Fehlerfall → Formularwerte zurückgeben (legacy-kompatibel)
        $this->title     = $_POST['title']     ?? '';
        $this->body      = $_POST['body']      ?? '';
        $this->topic_id  = $_POST['topic_id']  ?? '';
        $this->published = isset($_POST['published']) ? '1' : '0';
    }
}
