<?php
declare(strict_types=1);

namespace App\OOP\Controllers\Admin;

use App\OOP\Repositories\DbRepository;

/**
 * AdminUserController
 *
 * Zuständig für:
 * - Auflisten aller Benutzer (index)
 * - Löschen eines Benutzers (delete)
 */
class AdminUserController
{
    public function __construct(private DbRepository $db)
    {
    }

    /**
     * Liefert alle Benutzer für die Admin-Übersicht.
     *
     * @return array{admin_users: array} Liste der User (aufsteigend nach id)
     */
    public function index(): array
    {
        $admin_users = $this->db->selectAll('users', [], 'id ASC');
        return compact('admin_users');
    }

    /**
     * Löscht einen Benutzer und zeigt eine Flash-Message.
     *
     * @param int $id Benutzer-ID
     * @return void
     */
    public function delete(int $id): void
    {
        $this->db->delete('users', $id);

        $_SESSION['message'] = 'User wurde gelöscht';
        $_SESSION['type']    = 'success';

        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit;
    }
}
