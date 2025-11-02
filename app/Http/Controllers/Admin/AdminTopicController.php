<?php
declare(strict_types=1);

// Zweck: Admin-Controller zum Erstellen, Listen, Bearbeiten, Aktualisieren und Löschen von Topics mit Validierung, Slug-Erzeugung und Zugriffsschutz.

namespace App\Http\Controllers\Admin;

use App\Infrastructure\Repositories\DbRepository;

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

    // Formularansicht + Liste (rechte Spalte)
    public function create(): array
    {
        $this->ensureAdmin();
        return [
            'topic'  => $this->topic,
            'errors' => $this->errors,
            'topics' => $this->repo->selectAll('topics', [], 'id DESC'),
        ];
    }

    // Neuen Topic speichern (POST)
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

        // Eindeutiger Name?
        $exists = $this->repo->selectOne('topics', ['name' => $name]);
        if ($exists) {
            $this->errors[] = 'Ein Topic mit diesem Namen existiert bereits.';
        }

        // Fehler → zurück zur Create-Ansicht
        if ($this->errors) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old']    = ['name' => $name, 'description' => $desc];
            header('Location: ' . BASE_URL . '/public/admin/topics/create.php');
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

        // Flash + Redirect
        $_SESSION['message'] = 'Topic erfolgreich angelegt.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/admin/topics/index.php');
        exit;
    }

    // Liste (optional)
    public function index(): array
    {
        $this->ensureAdmin();
        return ['topics' => $this->repo->selectAll('topics', [], 'id DESC')];
    }

    // Bearbeitungs-View vorbereiten
    public function edit(int $id): array
    {
        $this->ensureAdmin();
        $topic = $this->repo->selectOne('topics', ['id' => $id]);
        if (!$topic) {
            $_SESSION['message'] = 'Topic nicht gefunden.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/admin/topics/index.php');
            exit;
        }
        return [
            'topic'  => $topic,
            'errors' => [],
            'topics' => $this->repo->selectAll('topics', [], 'id DESC'),
        ];
    }

    // Update (POST von edit.php)
    public function update(int $id, array $data): void
    {
        $this->ensureAdmin();

        $name = trim((string)($data['name'] ?? ''));
        $desc = trim((string)($data['description'] ?? ''));

        // Validierung
        if ($name === '') {
            $this->errors[] = 'Name ist erforderlich.';
        }

        // Name muss eindeutig sein (ohne sich selbst)
        $dup = $this->repo->selectOne('topics', ['name' => $name]);
        if ($dup && (int)$dup['id'] !== $id) {
            $this->errors[] = 'Ein Topic mit diesem Namen existiert bereits.';
        }

        // Fehler → zurück zur Edit-Ansicht
        if ($this->errors) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old']    = ['name' => $name, 'description' => $desc];
            header('Location: ' . BASE_URL . '/public/admin/topics/edit.php?id=' . $id);
            exit;
        }

        // Slug neu bilden
        $slug = $this->slugify($name);

        // Update speichern
        $this->repo->update('topics', $id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc,
        ]);

        // Flash + Redirect
        $_SESSION['message'] = 'Topic aktualisiert.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/admin/topics/index.php');
        exit;
    }

    // Löschen
    public function destroy(int $id): void
    {
        $this->ensureAdmin();
        $this->repo->delete('topics', $id);
        $_SESSION['message'] = 'Topic gelöscht.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/admin/topics/index.php');
        exit;
    }

    // Zugriffsschutz: nur Admins
    private function ensureAdmin(): void
    {
        if (!isset($_SESSION['id']) || empty($_SESSION['admin'])) {
            $_SESSION['message'] = 'Nur Admins haben Zugriff.';
            $_SESSION['type']    = 'error';
            header('Location: ' . BASE_URL . '/public/index.php');
            exit;
        }
    }

    // Slug-Helfer
    private function slugify(string $name): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        $s = strtolower((string)$s);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        $s = trim($s ?? '', '-');
        return $s === '' ? 'topic' : $s;
    }
}
