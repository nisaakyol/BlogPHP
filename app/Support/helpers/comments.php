<?php
// Zweck: Kommentare für einen Post als verschachtelten Baum rendern (inkl. Reply-Link)

use App\Http\Controllers\CommentController;
use App\Infrastructure\Repositories\DbRepository;

if (!function_exists('display_comments')) {
    function display_comments(int $postId): void
    {
        // Controller + Repository instanzieren
        $ctrl = new CommentController(new DbRepository());

        // Kommentarbaum für den Post laden
        $tree = $ctrl->treeForPost($postId);

        // Container öffnen
        echo '<div id="comments" class="comments">';

        // Leerer Zustand
        if (empty($tree)) {
            echo '<p>Keine Kommentare vorhanden.</p></div>';
            return;
        }

        // Rekursive Renderer-Closure für verschachtelte Kommentare
        $render = function (array $nodes) use (&$render) {
            echo '<ul class="comment-list">';

            foreach ($nodes as $n) {
                // Basiseigenschaften defensiv auslesen
                $username = isset($n['username']) ? (string)$n['username'] : 'Anonym';
                $created  = isset($n['created_at']) ? (string)$n['created_at'] : '';
                $ts       = $created !== '' ? strtotime($created) : false;
                $dateStr  = $ts ? date('d.m.Y H:i', $ts) : ($created ?: '');

                // Kommentartext (comment > body > '')
                $text = (string)($n['comment'] ?? $n['body'] ?? '');

                echo '<li class="comment">';

                // Meta: Autor + Datum
                echo '<div class="comment-meta">';
                echo '<strong>' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</strong> ';
                if ($dateStr !== '') {
                    echo '<span class="date">' . htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                echo '</div>';

                // Textkörper des Kommentars (mit Zeilenumbrüchen)
                echo '<div class="comment-body">'
                    . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'))
                    . '</div>';

                // Reply-Link setzt per JS das parent_id-Feld im Formular
                if (!empty($n['id'])) {
                    echo '<div class="comment-actions">'
                        . '<a href="#" class="reply" data-parent="' . (int)$n['id'] . '">Antworten</a>'
                        . '</div>';
                }

                // Kinder rekursiv rendern
                if (!empty($n['children']) && is_array($n['children'])) {
                    $render($n['children']);
                }

                echo '</li>';
            }

            echo '</ul>';
        };

        // Wurzelknoten rendern und Container schließen
        $render($tree);
        echo '</div>';
    }
}
