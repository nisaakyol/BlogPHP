<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\TopicRepository;

/**
 * TopicController
 *
 * Spiegelt das Legacy-Verhalten für Topics:
 * - bootInitialState(): Prefill der Formularwerte (bei ?id=…) und Liste aller Topics
 * - handleAdd(): Topic anlegen (POST)
 * - handleDelete(): Topic löschen (GET ?del_id=…)
 * - handleUpdate(): Topic aktualisieren (POST)
 *
 * Erwartete globale Helfer:
 * - adminOnly(), validateTopic(), BASE_URL
 */
class TopicController
{
    private TopicRepository $repo;

    // Legacy-Variablen, die die Views erwarten
    public string $table       = 'topics';
    public array  $errors      = [];
    public string $id          = '';
    public string $name        = '';
    public string $description = '';
    public array  $topics      = [];

    public function __construct()
    {
        $this->repo = new TopicRepository();
    }

    /**
     * Lädt Topics und befüllt bei ?id=… die Formularwerte.
     */
    public function bootInitialState(): void
    {
        $this->topics = $this->repo->all();

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

    /**
     * Verarbeitung: Topic anlegen (POST name="add-topic").
     * - Nur für Admins.
     * - validateTopic() liefert Fehlerliste (Legacy-kompatibel).
     * - Bei Erfolg: Flash & Redirect; sonst Formularwerte zurückgeben.
     */
    public function handleAdd(): void
    {
        if (!isset($_POST['add-topic'])) {
            return;
        }

        adminOnly();
        $this->errors = validateTopic($_POST);

        if (count($this->errors) === 0) {
            unset($_POST['add-topic']);

            $this->repo->create($_POST);

            $_SESSION['message'] = 'Topic wurde erfolgreich erstellt';
            $_SESSION['type']    = 'success';

            header('location: ' . BASE_URL . '/admin/topics/index.php');
            exit();
        }

        // Fehlerfall → Formularwerte erhalten
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
        if (!isset($_GET['del_id'])) {
            return;
        }

        adminOnly();

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
        if (!isset($_POST['update-topic'])) {
            return;
        }

        adminOnly();
        $this->errors = validateTopic($_POST);

        if (count($this->errors) === 0) {
            $id = (int) $_POST['id'];

            unset($_POST['update-topic'], $_POST['id']);

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
