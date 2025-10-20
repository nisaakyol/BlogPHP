<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

/**
 * AdminDashboardController
 *
 * Liefert einfache Kennzahlen für das Admin-Dashboard.
 * Zählt die Einträge in posts, topics und users.
 */
class AdminDashboardController
{
    /**
     * @param DbRepository $db Datenzugriffsschicht
     */
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Ermittelt Dashboard-Stats.
     *
     * @return array{posts:int, topics:int, users:int} Assoziatives Array mit Zählerständen
     */
    public function stats(): array
    {
        $posts  = count($this->db->selectAll('posts'));
        $topics = count($this->db->selectAll('topics'));
        $users  = count($this->db->selectAll('users'));

        return compact('posts', 'topics', 'users');
    }
}
