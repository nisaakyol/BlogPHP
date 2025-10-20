<?php
/**
 * Datei: app/helpers/comments.php
 * Zweck: Legacy-kompatibles Rendern der Kommentarstruktur (display_comments).
 * Robustheit:
 * - Nutzt Text aus $row['comment'] oder, falls vorhanden, $row['body'] (Fallback).
 * - Verhindert "Undefined array key" und "htmlspecialchars(): Passing null".
 * - Saubere UTF-8-Ausgabe.
 */

use App\OOP\Controllers\CommentController;
use App\OOP\Repositories\DbRepository;

if (!function_exists('display_comments')) {
    function display_comments(int $postId): void
    {
        $ctrl = new CommentController(new DbRepository());
        $tree = $ctrl->treeForPost($postId);

        echo '<div id="comments" class="comments">';
        if (empty($tree)) {
            echo '<p>Keine Kommentare vorhanden.</p></div>';
            return;
        }

        $render = function (array $nodes) use (&$render) {
            echo '<ul class="comment-list">';
            foreach ($nodes as $n) {
                // Sicherer Zugriff auf Felder
                $username = isset($n['username']) ? (string)$n['username'] : 'Anonym';
                $created  = isset($n['created_at']) ? (string)$n['created_at'] : '';
                $ts       = $created !== '' ? strtotime($created) : false;
                $dateStr  = $ts ? date('d.m.Y H:i', $ts) : ($created ?: '');

                // Kommentartext: bevorzugt 'comment', Fallback 'body', sonst leer
                $text = (string)($n['comment'] ?? $n['body'] ?? '');

                echo '<li class="comment">';

                echo '<div class="comment-meta">';
                echo '<strong>' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</strong> ';
                if ($dateStr !== '') {
                    echo '<span class="date">' . htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                echo '</div>';

                echo '<div class="comment-body">' .
                        nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) .
                     '</div>';

                // Reply-Link setzt parent_id per JS
                if (!empty($n['id'])) {
                    echo '<div class="comment-actions"><a href="#" class="reply" data-parent="' . (int)$n['id'] . '">Antworten</a></div>';
                }

                if (!empty($n['children']) && is_array($n['children'])) {
                    $render($n['children']);
                }

                echo '</li>';
            }
            echo '</ul>';
        };

        $render($tree);
        echo '</div>';
    }
}
