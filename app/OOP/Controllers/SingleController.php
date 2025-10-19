<?php
namespace App\OOP\Controllers;

class SingleController {
    public array $post = [];
    public array $topics = [];

    public function boot(): void {
        // Topics wie gewohnt
        $this->topics = function_exists('selectAll') ? selectAll('topics') : [];

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { $this->post = []; return; }

        // Falls Legacy-Helfer existieren: nutze sie
        if (function_exists('selectOne')) {
            // username wird in vielen Templates benötigt; wenn nicht vorhanden, via JOIN laden
            $p = selectOne('posts', ['id' => $id]);
            if ($p && empty($p['username'])) {
                $p = $this->loadWithUsernameJoin($id);
            }
            $this->post = $p ?: [];
            return;
        }

        // letzter Fallback: manuelles JOIN
        $this->post = $this->loadWithUsernameJoin($id) ?: [];
    }

    private function loadWithUsernameJoin(int $id): ?array {
        // nutzt die Legacy-DB-Funktion executeQuery
        if (!function_exists('executeQuery')) return null;
        $sql = "SELECT p.*, u.username
                  FROM posts p
                  LEFT JOIN users u ON u.id = p.user_id
                 WHERE p.id = ?
                 LIMIT 1";
        $st = executeQuery($sql, ['id' => $id]);
        return $st->get_result()->fetch_assoc() ?: null;
    }
}
