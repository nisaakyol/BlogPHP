<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

/**
 * SingleController
 *
 * Lädt ein einzelnes Posting für single.php:
 * - Topics via Legacy-Helfer (selectAll), falls vorhanden.
 * - Post via selectOne; falls username fehlt, wird per Join nachgeladen.
 * - Fallback: manuelles JOIN über executeQuery().
 */
class SingleController
{
    public array $post   = [];
    public array $topics = [];

    /**
     * Initialisiert Topics und den gewünschten Post (per $_GET['id']).
     */
    public function boot(): void
    {
        // Topics wie gewohnt (Legacy-Helfer, wenn vorhanden)
        $this->topics = function_exists('selectAll') ? selectAll('topics') : [];

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->post = [];
            return;
        }

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

        // Letzter Fallback: manuelles JOIN
        $this->post = $this->loadWithUsernameJoin($id) ?: [];
    }

    /**
     * Lädt einen Post inkl. Username via JOIN (Legacy executeQuery).
     *
     * @param int $id
     * @return array|null
     */
    private function loadWithUsernameJoin(int $id): ?array
    {
        // nutzt die Legacy-DB-Funktion executeQuery
        if (!function_exists('executeQuery')) {
            return null;
        }

        $sql = "SELECT p.*, u.username
                  FROM posts p
                  LEFT JOIN users u ON u.id = p.user_id
                 WHERE p.id = ?
                 LIMIT 1";

        // Hinweis: executeQuery liefert hier typischerweise ein mysqli_stmt
        $st = executeQuery($sql, ['id' => $id]);
        if (!is_object($st)) {
            return null;
        }

        $res = $st->get_result();
        if (!is_object($res)) {
            return null;
        }

        /** @var array<string, mixed>|null $row */
        $row = $res->fetch_assoc() ?: null;
        return $row ?: null;
    }
}
