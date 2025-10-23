<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

final class AdminTopicController
{
    public array $errors = [];
    public array $topic  = [
        'id'          => '',
        'name'        => '',
        'slug'        => '',
        'description' => '',
    ];

    public function __construct(private DbRepository $repo) {}

    /** Formularansicht + Liste für die rechte Spalte */
    public function create(): array
    {
        $this->ensureAdmin();
        return [
            'topic'  => $this->topic,
            'errors' => $this->errors,
            'topics' => $this->repo->selectAll('topics', [], 'id DESC'),
        ];
    }

    /** Neuen Topic speichern (POST) */
    public function store(array $data): void
    {
        $this->ensureAdmin();

        // Felder holen
        $name = trim((string)($data['name'] ?? ''));
        $desc = trim((string)($data['description'] ?? ''));

        // Validierung
        if ($name === '') {
            $this->errors[] = 'Name ist erforderlich.';
        } elseif (mb_strlen($name) > 150) {
            $this->errors[] = 'Name ist zu lang (max. 150 Zeichen).';
        }

        // eindeutiger Name?
        $exists = $this->repo->selectOne('topics', ['name' => $name]);
        if ($exists) {
            $this->errors[] = 'Ein Topic mit diesem Namen existiert bereits.';
        }

        if ($this->errors) {
            // zurück zur Create-Ansicht mit Fehlermeldungen
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old']    = ['name' => $name, 'description' => $desc];
            header('Location: ' . BASE_URL . '/admin/topics/create.php');
            exit;
        }

        // Slug generieren
        $slug = $this->slugify($name);

        // Speichern
        $this->repo->create('topics', [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc,
        ]);

        $_SESSION['message'] = 'Topic erfolgreich angelegt.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/admin/topics/index.php');
        exit;
    }

    /** Liste (falls du sie brauchst) */
    public function index(): array
    {
        $this->ensureAdmin();
        return ['topics' => $this->repo->selectAll('topics', [], 'id DESC')];
    }

    /** Bearbeitungs-View vorbereiten */
    public function edit(int $id): array
    {
        $this->ensureAdmin();
        $topic = $this->repo->selectOne('topics', ['id' => $id]);
        if (!$topic) {
            $_SESSION['message'] = 'Topic nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/admin/topics/index.php');
            exit;
        }
        return [
            'topic'  => $topic,
            'errors' => [],
            'topics' => $this->repo->selectAll('topics', [], 'id DESC'),
        ];
    }

    /** Update (POST von edit.php) */
    public function update(int $id, array $data): void
    {
        $this->ensureAdmin();

        $name = trim((string)($data['name'] ?? ''));
        $desc = trim((string)($data['description'] ?? ''));

        if ($name === '') {
            $this->errors[] = 'Name ist erforderlich.';
        }

        // Name muss eindeutig sein (ohne sich selbst)
        $dup = $this->repo->selectOne('topics', ['name' => $name]);
        if ($dup && (int)$dup['id'] !== $id) {
            $this->errors[] = 'Ein Topic mit diesem Namen existiert bereits.';
        }

        if ($this->errors) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old']    = ['name' => $name, 'description' => $desc];
            header('Location: ' . BASE_URL . '/admin/topics/edit.php?id=' . $id);
            exit;
        }

        $slug = $this->slugify($name);

        $this->repo->update('topics', $id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc,
        ]);

        $_SESSION['message'] = 'Topic aktualisiert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/admin/topics/index.php');
        exit;
    }

    /** Löschen */
    public function destroy(int $id): void
    {
        $this->ensureAdmin();
        $this->repo->delete('topics', $id);
        $_SESSION['message'] = 'Topic gelöscht.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/admin/topics/index.php');
        exit;
    }

    // ───────────────────────── intern ─────────────────────────

    private function ensureAdmin(): void
    {
        if (!isset($_SESSION['id']) || empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nur Admins haben Zugriff.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    private function slugify(string $name): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        $s = strtolower((string)$s);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        $s = trim($s ?? '', '-');
        return $s === '' ? 'topic' : $s;
    }
}
