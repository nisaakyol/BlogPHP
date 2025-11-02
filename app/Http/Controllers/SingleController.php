<?php
declare(strict_types=1);

// Zweck: Lädt auf der Single-Post-Seite die Topics sowie den veröffentlichten Post inklusive Autor- und Topic-Namen.

namespace App\Http\Controllers;

use App\Infrastructure\Core\DB;
use App\Infrastructure\Repositories\DbRepository;

class SingleController
{
    // View-Model: aktueller Post
    public array $post = [];

    // View-Model: Liste der Topics (z. B. für Sidebar)
    public array $topics = [];

    public function boot(): void
    {
        // Topics OOP-basiert laden (alphabetisch)
        $repo = new DbRepository();
        $this->topics = $repo->selectAll('topics', [], 'name ASC');

        // Post-ID aus Request holen und prüfen
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $this->post = [];
            return;
        }

        // Post inkl. username und topic_name via PDO-JOIN laden
        $this->post = $this->loadPostWithJoinPDO($id) ?? [];
    }

    /**
     * Lädt einen Post inkl. Username (und Topic-Name) via PDO-JOIN.
     *
     * @param int $id
     * @return array<string,mixed>|null
     */
    private function loadPostWithJoinPDO(int $id): ?array
    {
        $pdo = DB::pdo();

        // Nur veröffentlichte Posts liefern
        $sql = <<<SQL
        SELECT
            p.*,
            u.username,
            t.name AS topic_name
        FROM posts p
        LEFT JOIN users  u ON u.id = p.user_id
        LEFT JOIN topics t ON t.id = p.topic_id
        WHERE p.id = :id AND p.published = 1
        LIMIT 1
        SQL;

        $st = $pdo->prepare($sql);
        $st->execute([':id' => $id]);

        /** @var array<string,mixed>|false $row */
        $row = $st->fetch();
        return $row !== false ? $row : null;
    }
}
