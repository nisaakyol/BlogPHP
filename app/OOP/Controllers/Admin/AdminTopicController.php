<?php
namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

class AdminTopicController
{
    public function __construct(private DbRepository $db) {}

    /**
     * Liste aller Topics (für admin/topics/index.php)
     */
    public function index(): array
    {
        // ggf. Sortierung anpassen
        return $this->db->selectAll('topics', [], 'id DESC');
    }

    /**
     * Topic löschen (für admin/topics/index.php?del_id=...)
     */
    public function destroy(int $id): void
    {
        $this->db->delete('topics', $id);
        $_SESSION['message'] = 'Topic wurde erfolgreich gelöscht';
        $_SESSION['type']    = 'success';
        header('Location: index.php');
        exit;
    }

    // Optional – je nach Bedarf in create.php/edit.php verwenden:
    public function store(array $data): void
    {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];
        if ($name === '') $errors[] = 'Name wird benötigt.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['name'=>$name, 'description'=>$description];
            header('Location: create.php');
            exit;
        }

        $this->db->create('topics', ['name'=>$name, 'description'=>$description]);
        $_SESSION['message'] = 'Topic wurde erfolgreich erstellt';
        $_SESSION['type']    = 'success';
        header('Location: index.php');
        exit;
    }

    public function update(int $id, array $data): void
    {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];
        if ($name === '') $errors[] = 'Name wird benötigt.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = ['name'=>$name, 'description'=>$description, 'id'=>$id];
            header('Location: edit.php?id='.$id);
            exit;
        }

        $this->db->update('topics', $id, ['name'=>$name, 'description'=>$description]);
        $_SESSION['message'] = 'Topic updated erfolgreich';
        $_SESSION['type']    = 'success';
        header('Location: index.php');
        exit;
    }
}
