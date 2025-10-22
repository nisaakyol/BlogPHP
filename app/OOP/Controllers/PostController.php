<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\PostRepository;
use App\OOP\Repositories\CommentRepository;
use App\OOP\Services\UploadService;
use App\OOP\Services\EmailService;
use App\OOP\Services\AuthService;
use App\OOP\Services\AccessService;

/**
 * PostController
 *
 * Verhalten:
 * - Initialzustand/Prefill für Formulare (Topics/Posts laden, ggf. Post per id)
 * - Aktionen per GET: Delete, Publish-Toggle
 * - Aktionen per POST: Create, Update
 * - Normale User sehen/ändern nur eigene Posts; Admins sehen/ändern alle
 * - Flash-Messages/Redirects wie gehabt
 */
class PostController
{
    private PostRepository $repo;
    private AccessService  $access;
    private AuthService    $auth;
    private string         $root;

    // Zustand, den die Views erwarten (Public-Properties wie im Legacy-Code):
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
     * Hinweis: Wir instantiieren die benötigten Services hier direkt,
     * damit der Controller drop-in bleibt. Wenn du DI bevorzugst,
     * gib $repo/$access/$auth einfach im Konstruktor rein.
     */
    public function __construct(string $rootPath)
    {
        $this->root   = $rootPath;
        $this->repo   = new PostRepository();
        $this->auth   = new AuthService();

        // AccessService benötigt Repos für Ownership-Checks:
        $this->access = new AccessService(
            $this->auth,
            $this->repo,
            new CommentRepository()
        );
    }

    /**
     * Initialbefüllung wie im Legacy-File – mit Rechte-Filter:
     * - Lädt Topics (für Formulare)
     * - Lädt Posts: Admin → alle, User → nur eigene
     * - Befüllt bei ?id=… die Formularfelder (nur wenn Zugriff erlaubt)
     */
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

    /**
     * Löschen per GET-Parameter (?delete_id=…)
     * - erfordert Login
     * - Ownership-Check
     * - Flash-Message + Redirect
     */
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

    /**
     * Publish/Unpublish per GET (?published=0/1&p_id=…)
     * - nur Admin
     * - Flash-Message + Redirect
     */
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

    /**
     * Erstellen bei POST (name="add-post")
     * - Login erforderlich
     * - validatePost($_POST)
     * - Bild-Upload via UploadService (identische Fehlermeldungen)
     * - Admin: published optional; User: Email an Admin + published=0
     * - Bei Fehlern: Formularwerte zurück in Properties (Legacy-kompatibel)
     */
    public function handleCreateIfPosted(): void
    {
        if (!isset($_POST['add-post'])) {
            return;
        }

        AccessService::requireUser();
        $this->errors = validatePost($_POST);

        // Bild hochladen – exakt gleiche Fehlermeldungen wie Legacy
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

                // >>> HIER Status setzen (Freigabe-Workflow)
                if (!empty($_SESSION['id']) && !empty($_POST['title'])) {
                    if (!empty($_POST['published'])) { unset($_POST['published']); } // User setzt publish nicht
                    $_POST['status']    = 'submitted';  // wenn Häkchen „zur Freigabe“
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

        // Fehlerfall → Formularwerte zurückgeben – exakt wie Legacy
        $this->title     = $_POST['title']     ?? '';
        $this->topic_id  = $_POST['topic_id']  ?? '';
        $this->body      = $_POST['body']      ?? '';
        $this->published = isset($_POST['published']) ? '1' : '0';
    }

    /**
     * Aktualisieren bei POST (name="update-post")
     * - Login + Ownership-Check
     * - validatePost($_POST)
     * - Bild optional hochladen (gleiches Fehlermuster wie im übergebenen Code)
     * - Update + Flash + Redirect
     * - Bei Fehlern: Formularwerte in Properties
     */
    public function handleUpdateIfPosted(): void
    {
        if (!isset($_POST['update-post'])) {
            return;
        }

        AccessService::requireUser();
        $this->errors = validatePost($_POST);

        // Permission vor dem eigentlichen Update prüfen
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0 || !$this->access->canManagePost($id)) {
            $_SESSION['message'] = 'Nicht erlaubt';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/posts/index.php');
            exit();
        }

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

        // Fehlerfall → Formularwerte zurückgeben – exakt wie Legacy
        $this->title     = $_POST['title']     ?? '';
        $this->body      = $_POST['body']      ?? '';
        $this->topic_id  = $_POST['topic_id']  ?? '';
        $this->published = isset($_POST['published']) ? '1' : '0';
    }
}
