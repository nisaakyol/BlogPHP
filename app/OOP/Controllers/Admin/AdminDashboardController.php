<?php
namespace App\OOP\Controllers\Admin;
use App\OOP\Repositories\DbRepository;

class AdminDashboardController {
    public function __construct(private DbRepository $db) {}
    public function stats(): array {
        $posts  = count($this->db->selectAll('posts'));
        $topics = count($this->db->selectAll('topics'));
        $users  = count($this->db->selectAll('users'));
        return compact('posts','topics','users');
    }
}
