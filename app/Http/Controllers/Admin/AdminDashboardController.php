<?php
declare(strict_types=1);

// Zweck: Liefert einfache Zählerstatistiken (Posts, Topics, Users) für das Admin-Dashboard.

namespace App\Http\Controllers\Admin;

use App\Infrastructure\Repositories\DbRepository;

class AdminDashboardController
{
    // Repo via Konstruktor-Injection
    public function __construct(private DbRepository $db)
    {
    }

    // Ermittelt Dashboard-Stats und gibt sie als kompaktes Array zurück
    public function stats(): array
    {
        // Anzahl der Datensätze je Tabelle ermitteln
        $posts  = count($this->db->selectAll('posts'));
        $topics = count($this->db->selectAll('topics'));
        $users  = count($this->db->selectAll('users'));

        // Kompakte Rückgabe
        return compact('posts', 'topics', 'users');
    }
}
