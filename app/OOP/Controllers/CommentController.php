<?php
declare(strict_types=1);

namespace App\OOP\Controllers;

use App\OOP\Repositories\CommentRepository;

/**
 * Datei: App/OOP/Controllers/CommentController.php
 *
 * Zweck:
 * - Liest POST-Felder, prüft Pflichtfelder,
 *   legt Kommentar an (inkl. optionaler parent_id) und leitet zu single.php?id=... um.
 * - Fehlerfall: einfache Ausgabe per echo (kompatibel zum bisherigen Verhalten).
 */
final class CommentController
{
    /**
     * Verarbeitet das Kommentarformular (nur bei POST).
     * - Erwartet Felder: username, comment, post_id, optional parent_id
     * - Bei Erfolg: Redirect auf single.php?id={post_id}
     * - Bei Fehler: echo-Ausgabe (wie zuvor)
     *
     * @return void
     */
    public function createFromPost(): void
    {
        // Nur POST verarbeiten; GET tut nichts (wie vorher)
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        // Rohwerte aus $_POST (bewusst minimal transformiert; Validierung wie zuvor)
        $username  = $_POST['username']  ?? '';
        $comment   = $_POST['comment']   ?? '';
        $parentRaw = $_POST['parent_id'] ?? null;
        $postRaw   = $_POST['post_id']   ?? null;

        // parent_id ist optional; leere Strings als null behandeln
        $parentId = ($parentRaw === '' || $parentRaw === null) ? null : (int) $parentRaw;
        $postId   = (int) $postRaw;

        // Pflichtfelder prüfen (wie zuvor)
        if ($username !== '' && $comment !== '') {
            $ok = CommentRepository::create($username, $comment, $parentId, $postId);

            if ($ok) {
                header('Location: single.php?id=' . $postId);
                exit;
            }

            // Fehlerfall: identisch zum bisherigen Verhalten (schlichte Ausgabe)
            echo 'Error: Insert failed.';
            return;
        }

        // Pflichtfelder nicht vollständig: ebenfalls schlichte Ausgabe
        echo 'Please fill in all fields.';
    }
}
