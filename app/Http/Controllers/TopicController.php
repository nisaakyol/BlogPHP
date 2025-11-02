<?php
declare(strict_types=1);

// Zweck: Verwalten der Topics im Admin-Bereich (Listen, Anlegen, Aktualisieren, Löschen) inklusive Formularvalidierung und Redirects.

namespace App\Http\Controllers;

use App\Infrastructure\Repositories\TopicRepository; // Hinweis: vorher stand fälschlich "AApp\..." – korrigiert

class TopicController
{
    // Repository für DB-Operationen
    private TopicRepository $repo;

    public string $table       = 'topics';
    public array  $errors      = [];
    public string $id          = '';
    public string $name        = '';
    public string $description = '';
    public array  $topics      = [];

    public function __construct()
    {
        // Repo instanziieren
        $this->repo = new TopicRepository();
    }

    // Initialzustand für die Maske: Topics laden und ggf. Formularfelder vorbefüllen
    public function bootInitialState(): void
    {
        // Alle Topics laden (für Liste/Sidebar/Formulare)
        $this->topics = $this->repo->all();

        // Wenn ?id=… gesetzt ist, Felder für das Bearbeiten vorbefüllen
        if (isset($_GET['id'])) {
            $id    = (int) $_GET['id'];
            $topic = $this->repo->findById($id);
            if ($topic) {
                $this->id          = (string) $topic['id'];
                $this->name        = (string) $topic['name'];
                $this->description = (string) $topic['description'];
            }
        }
    }

    public function handleAdd(): void
    {
        // Nur reagieren, wenn Formular abgesendet wurde
        if (!isset($_POST['add-topic'])) {
            return;
        }

        adminOnly(); // Zugriffsschutz
        $this->errors = validateTopic($_POST); // Validierung

        // Erfolgsfall: anlegen, flashen, redirecten
        if (count($this->errors) === 0) {
            unset($_POST['add-topic']);

            $this->repo->create($_POST);

            $_SESSION['message'] = 'Topic wurde erfolgreich erstellt';
            $_SESSION['type']    = 'success';

            header('location: ' . BASE_URL . '/admin/topics/index.php');
            exit();
        }

        // Fehlerfall → Formularwerte erhalten (für Re-Rendering)
        $this->name        = $_POST['name']        ?? '';
        $this->description = $_POST['description'] ?? '';
    }

    /**
     * Verarbeitung: Topic löschen (GET ?del_id=…).
     * - Nur für Admins.
     * - Flash & Redirect.
     */
    public function handleDelete(): void
    {
        // Nur reagieren, wenn Parameter gesetzt
        if (!isset($_GET['del_id'])) {
            return;
        }

        adminOnly(); // Zugriffsschutz

        $id = (int) $_GET['del_id'];
        $this->repo->delete($id);

        $_SESSION['message'] = 'Topic wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';

        header('location: ' . BASE_URL . '/admin/topics/index.php');
        exit();
    }

    /**
     * Verarbeitung: Topic aktualisieren (POST name="update-topic").
     * - Nur für Admins.
     * - validateTopic(), dann Update + Flash + Redirect; sonst Werte zurückgeben.
     */
    public function handleUpdate(): void
    {
        // Nur reagieren, wenn Formular abgesendet wurde
        if (!isset($_POST['update-topic'])) {
            return;
        }

        adminOnly(); // Zugriffsschutz
        $this->errors = validateTopic($_POST); // Validierung

        // Erfolgsfall: Update durchführen
        if (count($this->errors) === 0) {
            $id = (int) $_POST['id'];

            unset($_POST['update-topic'], $_POST['id']); // Steuerfelder entfernen

            $this->repo->update($id, $_POST);

            $_SESSION['message'] = 'Topic updated erfolgreich';
            $_SESSION['type']    = 'success';

            header('location: ' . BASE_URL . '/admin/topics/index.php');
            exit();
        }

        // Fehlerfall → Formularwerte erhalten
        $this->id          = $_POST['id']          ?? '';
        $this->name        = $_POST['name']        ?? '';
        $this->description = $_POST['description'] ?? '';
    }
}
