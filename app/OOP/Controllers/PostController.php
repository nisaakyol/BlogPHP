<?php
namespace App\OOP\Controllers;

use App\OOP\Repositories\PostRepository;
use App\OOP\Services\UploadService;
use App\OOP\Services\EmailService;

class PostController {
    private PostRepository $repo;
    private string $root;

    // Zustand, den deine Views erwarten:
    public array $errors = [];
    public string $table = 'posts';
    public array $topics = [];
    public array $posts = [];
    public string $id = "";
    public string $title = "";
    public string $body = "";
    public string $topic_id = "";
    public string $published = "";

    public function __construct(string $rootPath) {
        $this->repo = new PostRepository();
        $this->root = $rootPath;
    }

    /** Initialbefüllung wie im Legacy-File. */
    public function bootInitialState(): void {
        $this->topics = $this->repo->topics();
        $this->posts  = $this->repo->allOrdered();

        if (isset($_GET['id'])) {
            $post = $this->repo->findById((int)$_GET['id']);
            if ($post) {
                $this->id        = (string)$post['id'];
                $this->title     = $post['title'];
                $this->body      = $post['body'];
                $this->topic_id  = (string)$post['topic_id'];
                $this->published = (string)$post['published'];
            }
        }
    }

    public function handleDeleteIfRequested(): void {
        if (isset($_GET['delete_id'])) {
            usersOnly();
            $this->repo->delete((int)$_GET['delete_id']);
            $_SESSION['message'] = "Post wurde erfolgreich gelöscht";
            $_SESSION['type']    = "success";
            header("location: " . BASE_URL . "/admin/posts/index.php");
            exit();
        }
    }

    public function handlePublishToggleIfRequested(): void {
        if (isset($_GET['published']) && isset($_GET['p_id'])) {
            adminOnly();
            $published = (int)$_GET['published'];
            $p_id      = (int)$_GET['p_id'];
            $this->repo->setPublished($p_id, $published);
            $_SESSION['message'] = "Post published Status geändert!";
            $_SESSION['type']    = "success";
            header("location: " . BASE_URL . "/admin/posts/index.php");
            exit();
        }
    }

    public function handleCreateIfPosted(): void {
        if (!isset($_POST['add-post'])) return;

        usersOnly();
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
            $_POST['user_id'] = $_SESSION['id'];
            $_POST['body']    = htmlentities($_POST['body']);

            if (!empty($_SESSION['admin'])) {
                $_POST['published'] = isset($_POST['published']) ? 1 : 0;
                $this->repo->create($_POST);
                $_SESSION['message'] = "Post created successfuly";
            } else {
                $_POST['AdminPublish'] = isset($_POST['AdminPublish']) ? 1 : 0;
                EmailService::sendPublish($_POST, $this->root);
                unset($_POST['AdminPublish']);
                $_POST['published'] = 0;
                $this->repo->create($_POST);
                $_SESSION['message'] = "Post wurde zur Veröffentlichung versandt";
            }

            $_SESSION['type'] = "success";
            header("location: " . BASE_URL . "/admin/posts/index.php");
            exit();
        } else {
            // Formularwerte zurückgeben – exakt wie Legacy
            $this->title     = $_POST['title'] ?? '';
            $this->topic_id  = $_POST['topic_id'] ?? '';
            $this->body      = $_POST['body'] ?? '';
            $this->published = isset($_POST['published']) ? '1' : '0';
        }
    }

    public function handleUpdateIfPosted(): void {
        if (!isset($_POST['update-post'])) return;

        usersOnly();
        $this->errors = validatePost($_POST);

        if (!empty($_FILES['image']['name'])) {
            [$ok, $imageName] = UploadService::moveImage($this->root, $_FILES['image']);
            if ($ok) {
                $_POST['image'] = $imageName;
            } else {
                $this->errors[] = "Fehler beim Upload des Bilds";
            }
        } else {
            $this->errors[] = "Post Bild benötigt";
        }

        if (count($this->errors) === 0) {
            $id = (int)$_POST['id'];
            unset($_POST['update-post'], $_POST['id']);
            $_POST['user_id']  = $_SESSION['id'];
            $_POST['published']= isset($_POST['published']) ? 1 : 0;
            $_POST['body']     = htmlentities($_POST['body']);

            $this->repo->update($id, $_POST);
            $_SESSION['message'] = "Post update erfolgreich";
            $_SESSION['type']    = "success";
            header("location: " . BASE_URL . "/admin/posts/index.php");
            exit();
        } else {
            // Formularwerte zurückgeben – exakt wie Legacy
            $this->title     = $_POST['title'] ?? '';
            $this->body      = $_POST['body'] ?? '';
            $this->topic_id  = $_POST['topic_id'] ?? '';
            $this->published = isset($_POST['published']) ? '1' : '0';
        }
    }
}
