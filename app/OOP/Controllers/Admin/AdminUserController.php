<?php
namespace App\OOP\Controllers\Admin;
use App\OOP\Repositories\DbRepository;

class AdminUserController {
    public function __construct(private DbRepository $db) {}

    public function index(): array {
        $admin_users = $this->db->selectAll('users', [], 'id ASC');
        return compact('admin_users');
    }

    public function delete(int $id): void {
        $this->db->delete('users', $id);
        $_SESSION['message'] = 'User wurde gelöscht';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit;
    }
}
