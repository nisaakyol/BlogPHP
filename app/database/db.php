<?php
/* Veränderungsdatum: 15.10.2024
   Diese Datei beinhaltet die Logik für die Datenbank-Abfragen für das Erstellen, Verändern und Löschen
   von Posts, Topics und Comments. (Legacy API beibehalten, intern OOP-Repo)
*/

// ---------------- Session + Timeout (unverändert) ----------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Wenn die letzte Aktivität > 30 Min her ist: Session leeren + Reload-Hinweis
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    echo '<script>alert("Session abgelaufen. Die Seite wird neu geladen.")</script>';
    echo '<script>window.location.reload();</script>';
    exit();
}
$_SESSION['last_activity'] = time();

// ---------------- DB-Connect (Legacy mysqli, bleibt bestehen) ----------------
require_once __DIR__ . '/connect.php';

// ---------------- OOP-Bootstrap & Repo laden ----------------
$__root      = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');
$__topBoot   = $__root . "/OOP/bootstrap.php";
$__legacyDB  = $__root . "/OOP/Core/LegacyDB.php";
$__repoDB    = $__root . "/OOP/Repositories/DbRepository.php";

if (is_file($__topBoot))  { require_once $__topBoot; }
if (is_file($__legacyDB)) { require_once $__legacyDB; }
if (is_file($__repoDB))   { require_once $__repoDB; }

use App\OOP\Repositories\DbRepository;

static $___db_singleton = null;
if ($___db_singleton === null) { $___db_singleton = new DbRepository(); }
$__db = $___db_singleton;

/* ================================================================
   ===============  Legacy-API (Funktionsnamen gleich)  ===========
   ================================================================ */

// Führt eine SQL-Abfrage aus und bindet alle Parameter (Typen = 's' wie legacy)
if (!function_exists('executeQuery')) {
    function executeQuery($sql, $data)
    {
        global $__db;
        return $__db->executeQuery($sql, (array)$data);
    }
}

if (!function_exists('selectAll')) {
    function selectAll($table, $conditions = [], $orderBy = '')
    {
        global $__db;
        return $__db->selectAll($table, (array)$conditions, (string)$orderBy);
    }
}

if (!function_exists('selectOne')) {
    function selectOne($table, $conditions = [], $orderBy = '')
    {
        global $__db;
        return $__db->selectOne($table, (array)$conditions, (string)$orderBy);
    }
}

if (!function_exists('create')) {
    function create($table, $data)
    {
        global $__db;
        return $__db->create($table, (array)$data);
    }
}

if (!function_exists('update')) {
    function update($table, $id, $data)
    {
        global $__db;
        return $__db->update($table, (int)$id, (array)$data);
    }
}

if (!function_exists('delete')) {
    function delete($table, $id)
    {
        global $__db;
        return $__db->delete($table, (int)$id);
    }
}

if (!function_exists('getPublishedPosts')) {
    function getPublishedPosts()
    {
        global $__db;
        return $__db->getPublishedPosts();
    }
}

if (!function_exists('getPostsByTopicId')) {
    function getPostsByTopicId($topic_id)
    {
        global $__db;
        return $__db->getPostsByTopicId((int)$topic_id);
    }
}

if (!function_exists('searchPosts')) {
    function searchPosts($term)
    {
        global $__db;
        return $__db->searchPosts((string)$term);
    }
}

// ---------------- Kommentar-Rendering (Ausgabe unverändert) ----------------
if (!function_exists('display_comments')) {
    function display_comments($post_id, $parent_id = null, $indent = 0)
    {
        global $__db, $conn;

        $records = $__db->fetchCommentsForPost((int)$post_id, $parent_id === null ? null : (int)$parent_id);

        if ($records === null) {
            echo "Failed to prepare statement: " . ($conn ? $conn->error : 'unknown');
            return;
        }

        if (count($records) > 0) {
            $root_comments = [];
            $child_comments = [];

            foreach ($records as $row) {
                if ((int)$row['parent_id'] === 0) {
                    $root_comments[] = $row;
                } else {
                    $child_comments[$row['parent_id']][] = $row;
                }
            }

            foreach ($root_comments as $comment) {
                display_comment_item($comment, $child_comments, $indent);
            }
        } else {
            if ($parent_id === null) {
                echo "<div style='margin-left: " . ($indent * 20) . "px;'>Keine Kommentare vorhanden.</div>";
            }
        }
    }
}

if (!function_exists('display_comment_item')) {
    function display_comment_item($comment, $child_comments, $indent)
    {
        ?>
        <div class="comment-item" style="margin-left: <?php echo $indent * 20; ?>px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; background-color: #f9f9f9;">
            <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div style="font-weight: bold; color: #333;">
                    <?php echo htmlspecialchars($comment['username']); ?>
                </div>
                <div style="font-size: 0.85em; color: #777;">
                    <?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?>
                </div>
            </div>
            <div class="comment-body" style="margin-bottom: 10px; color: #444; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
            </div>
            <div class="comment-footer" style="margin-top: 10px;">
                <a href='#' class='reply-link' data-id='<?php echo $comment['id']; ?>' style="color: #007BFF; text-decoration: none;">Antworten</a>
            </div>
        </div>
        <?php
        if (isset($child_comments[$comment['id']])) {
            foreach ($child_comments[$comment['id']] as $child_comment) {
                display_comment_item($child_comment, $child_comments, $indent + 2);
            }
        }
    }
}
