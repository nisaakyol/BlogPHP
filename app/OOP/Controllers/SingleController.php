<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Core\DB;
use App\OOP\Repositories\DbRepository;

/**
 * SingleController
 *
 * Lädt ein einzelnes Posting für single.php:
 * - Topics via DbRepository (OOP).
 * - Post via PDO mit JOIN auf users (und optional topics).
 * - Kein executeQuery()/mysqli mehr.
 */
class SingleController
{
    /** @var array<string,mixed> */
    public array $post = [];

    /** @var array<int,array<string,mixed>> */
    public array $topics = [];

    /**
     * Initialisiert Topics und den gewünschten Post (per $_GET['id']).
     */
    public function boot(): void
    {
        // Topics OOP-basiert laden
        $repo = new DbRepository();
        $this->topics = $repo->selectAll('topics', [], 'name ASC');

        // Post-ID aus Request
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $this->post = [];
            return;
        }

        // Post inkl. username (und topic_name) via PDO laden
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
