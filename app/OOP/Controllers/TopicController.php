<?php
namespace App\OOP\Controllers;

use App\OOP\Repositories\TopicRepository;

class TopicController {
    private TopicRepository $repo;

    // Legacy-Variablen, die deine Views erwarten
    public string $table = 'topics';
    public array  $errors = [];
    public string $id = '';
    public string $name = '';
    public string $description = '';
    public array  $topics = [];

    public function __construct() {
        $this->repo = new TopicRepository();
    }

    public function bootInitialState(): void {
        $this->topics = $this->repo->all();

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $topic = $this->repo->findById($id);
            if ($topic) {
                $this->id          = (string)$topic['id'];
                $this->name        = $topic['name'];
                $this->description = $topic['description'];
            }
        }
    }

    public function handleAdd(): void {
        if (!isset($_POST['add-topic'])) return;

        adminOnly();
        $this->errors = validateTopic($_POST);

        if (count($this->errors) === 0) {
            unset($_POST['add-topic']);
            $this->repo->create($_POST);
            $_SESSION['message'] = 'Topic wurde erfolgreich erstellt';
            $_SESSION['type']    = 'success';
            header('location: ' . BASE_URL . '/admin/topics/index.php');
            exit();
        } else {
            $this->name        = $_POST['name']        ?? '';
            $this->description = $_POST['description'] ?? '';
        }
    }

    public function handleDelete(): void {
        if (!isset($_GET['del_id'])) return;

        adminOnly();
        $id = (int)$_GET['del_id'];
        $this->repo->delete($id);
        $_SESSION['message'] = 'Topic wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';
        header('location: ' . BASE_URL . '/admin/topics/index.php');
        exit();
    }

    public function handleUpdate(): void {
        if (!isset($_POST['update-topic'])) return;

        adminOnly();
        $this->errors = validateTopic($_POST);

        if (count($this->errors) === 0) {
            $id = (int)$_POST['id'];
            unset($_POST['update-topic'], $_POST['id']);
            $this->repo->update($id, $_POST);
            $_SESSION['message'] = 'Topic updated erfolgreich';
            $_SESSION['type']    = 'success';
            header('location: ' . BASE_URL . '/admin/topics/index.php');
            exit();
        } else {
            $this->id          = $_POST['id']          ?? '';
            $this->name        = $_POST['name']        ?? '';
            $this->description = $_POST['description'] ?? '';
        }
    }
}
