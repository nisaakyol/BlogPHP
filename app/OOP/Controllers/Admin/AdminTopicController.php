<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

/**
 * AdminTopicController
 *
 * Zuständig für:
 * - Auflisten aller Topics (index)
 * - Löschen (destroy)
 * - Erstellen (store)
 * - Aktualisieren (update)
 *
 * Hinweise:
 * - Validierung ist bewusst minimal gehalten (nur Pflichtfeld "name"),
 *   um bestehendes Verhalten nicht zu ändern.
 * - Redirect-Ziele bleiben relativ (index.php/create.php/edit.php),
 *   wie im ursprünglichen Code.
 */
class AdminTopicController
{
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Liste aller Topics (für admin/topics/index.php).
     *
     * @return array Liste der Topics, absteigend nach id sortiert
     */
    public function index(): array
    {
        // ggf. Sortierung anpassen
        return $this->db->selectAll('topics', [], 'id DESC');
    }

    /**
     * Topic löschen (für admin/topics/index.php?del_id=...).
     *
     * @param int $id Topic-ID
     * @return void
     */
    public function destroy(int $id): void
    {
        $this->db->delete('topics', $id);

        $_SESSION['message'] = 'Topic wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';

        header('Location: index.php');
        exit;
    }

    /**
     * Topic erstellen (genutzt von create.php).
     *
     * @param array $data Form-Daten (typisch $_POST)
     * @return void
     */
    public function store(array $data): void
    {
        $name        = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name wird benötigt.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['name' => $name, 'description' => $description];

            header('Location: create.php');
            exit;
        }

        $this->db->create('topics', [
            'name'        => $name,
            'description' => $description,
        ]);

        $_SESSION['message'] = 'Topic wurde erfolgreich erstellt';
        $_SESSION['type']    = 'success';

        header('Location: index.php');
        exit;
    }

    /**
     * Topic aktualisieren (genutzt von edit.php).
     *
     * @param int   $id   Topic-ID
     * @param array $data Form-Daten (typisch $_POST)
     * @return void
     */
    public function update(int $id, array $data): void
    {
        $name        = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name wird benötigt.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = [
                'name'        => $name,
                'description' => $description,
                'id'          => $id,
            ];

            header('Location: edit.php?id=' . $id);
            exit;
        }

        $this->db->update('topics', $id, [
            'name'        => $name,
            'description' => $description,
        ]);

        $_SESSION['message'] = 'Topic updated erfolgreich';
        $_SESSION['type']    = 'success';

        header('Location: index.php');
        exit;
    }
}
