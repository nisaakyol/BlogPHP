<?php
/*
 * Datei: app/database/functions.php (Beispielname)
 * Zweck: Legacy-DB-API beibehalten (Funktionsnamen), intern an OOP-Repository anbinden.
 *        Enthält außerdem Session-/Timeout-Handling und Kommentar-Rendering.
 *
 * Hinweise:
 * - Session-Timeout: 30 Minuten Inaktivität -> Session wird beendet, Seite neu geladen.
 * - DB-Anbindung: Legacy (mysqli) bleibt bestehen via connect.php.
 * - OOP: DbRepository wird als Singleton ($__db) genutzt; Funktionshüllen delegieren daran.
 * - Ausgabe/Rendering nutzt htmlspecialchars() für sichere Darstellung.
 */

/* --------------------------------------------------------------------------
 * Session + Timeout (unverändert, nur formatiert)
 * ------------------------------------------------------------------------ */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Inaktivität > 30 Minuten -> Session invalidieren und Seite neu laden
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
  session_unset();
  session_destroy();
  echo '<script>alert("Session abgelaufen. Die Seite wird neu geladen.")</script>';
  echo '<script>window.location.reload();</script>';
  exit();
}
$_SESSION['last_activity'] = time();

/* --------------------------------------------------------------------------
 * DB-Connect (Legacy mysqli)
 * ------------------------------------------------------------------------ */
require_once __DIR__ . '/connect.php'; // Stellt $conn bereit (mysqli)

/* --------------------------------------------------------------------------
 * OOP-Bootstrap & Repository laden
 * ------------------------------------------------------------------------ */
$__root     = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');
$__topBoot  = $__root . "/OOP/bootstrap.php";
$__legacyDB = $__root . "/OOP/Core/LegacyDB.php";
$__repoDB   = $__root . "/OOP/Repositories/DbRepository.php";

if (is_file($__topBoot))  { require_once $__topBoot; }
if (is_file($__legacyDB)) { require_once $__legacyDB; }
if (is_file($__repoDB))   { require_once $__repoDB; }

use App\OOP\Repositories\DbRepository;

// Einfaches Singleton des Repos für die Legacy-Funktionen
static $___db_singleton = null;
if ($___db_singleton === null) {
  $___db_singleton = new DbRepository();
}
$__db = $___db_singleton;

/* ==========================================================================
 * Legacy-API: Funktionshüllen mit identischen Namen/Signaturen
 *            Intern wird an DbRepository delegiert.
 * ======================================================================== */

/**
 * Führt eine SQL-Abfrage aus und bindet alle Parameter (Legacy: alle 's').
 *
 * @param string $sql
 * @param array  $data
 * @return mixed
 */
if (!function_exists('executeQuery')) {
  function executeQuery($sql, $data) {
    global $__db;
    return $__db->executeQuery((string)$sql, (array)$data);
  }
}

/**
 * Wählt alle Datensätze einer Tabelle (mit optionalen Bedingungen/Order).
 *
 * @param string $table
 * @param array  $conditions
 * @param string $orderBy
 * @return array
 */
if (!function_exists('selectAll')) {
  function selectAll($table, $conditions = [], $orderBy = '') {
    global $__db;
    return $__db->selectAll((string)$table, (array)$conditions, (string)$orderBy);
  }
}

/**
 * Wählt genau einen Datensatz (ersten Treffer) nach Bedingungen/Order.
 *
 * @param string $table
 * @param array  $conditions
 * @param string $orderBy
 * @return array|null
 */
if (!function_exists('selectOne')) {
  function selectOne($table, $conditions = [], $orderBy = '') {
    global $__db;
    return $__db->selectOne((string)$table, (array)$conditions, (string)$orderBy);
  }
}

/**
 * Insert in Tabelle.
 *
 * @param string $table
 * @param array  $data
 * @return int|false  Neue ID oder false
 */
if (!function_exists('create')) {
  function create($table, $data) {
    global $__db;
    return $__db->create((string)$table, (array)$data);
  }
}

/**
 * Update per ID.
 *
 * @param string $table
 * @param int    $id
 * @param array  $data
 * @return int|false  Anzahl betroffener Zeilen oder false
 */
if (!function_exists('update')) {
  function update($table, $id, $data) {
    global $__db;
    return $__db->update((string)$table, (int)$id, (array)$data);
  }
}

/**
 * Delete per ID.
 *
 * @param string $table
 * @param int    $id
 * @return int|false
 */
if (!function_exists('delete')) {
  function delete($table, $id) {
    global $__db;
    return $__db->delete((string)$table, (int)$id);
  }
}

/**
 * Alle veröffentlichten Posts.
 *
 * @return array
 */
if (!function_exists('getPublishedPosts')) {
  function getPublishedPosts() {
    global $__db;
    return $__db->getPublishedPosts();
  }
}

/**
 * Posts via Topic-ID.
 *
 * @param int $topic_id
 * @return array
 */
if (!function_exists('getPostsByTopicId')) {
  function getPostsByTopicId($topic_id) {
    global $__db;
    return $__db->getPostsByTopicId((int)$topic_id);
  }
}

/**
 * Suche in Posts (Titel/Body).
 *
 * @param string $term
 * @return array
 */
if (!function_exists('searchPosts')) {
  function searchPosts($term) {
    global $__db;
    return $__db->searchPosts((string)$term);
  }
}

/* --------------------------------------------------------------------------
 * Kommentar-Rendering (Ausgabe unverändert; leicht defensiv gehärtet)
 * ------------------------------------------------------------------------ */

/**
 * Rendert die Kommentarstruktur (Root + rekursiv Kinder) zu einem Post.
 *
 * @param int      $post_id
 * @param int|null $parent_id
 * @param int      $indent        Anzahl Einrückungsstufen (×20px)
 * @return void
 */
if (!function_exists('display_comments')) {
  function display_comments($post_id, $parent_id = null, $indent = 0) {
    global $__db, $conn;

    $records = $__db->fetchCommentsForPost((int)$post_id, $parent_id === null ? null : (int)$parent_id);

    if ($records === null) {
      $err = (isset($conn) && $conn instanceof mysqli) ? $conn->error : 'unknown';
      echo "Failed to prepare statement: " . htmlspecialchars($err, ENT_QUOTES, 'UTF-8');
      return;
    }

    if (count($records) > 0) {
      $root_comments  = [];
      $child_comments = [];

      foreach ($records as $row) {
        $pid = (int)($row['parent_id'] ?? 0);
        if ($pid === 0) {
          $root_comments[] = $row;
        } else {
          $child_comments[$pid][] = $row;
        }
      }

      foreach ($root_comments as $comment) {
        display_comment_item($comment, $child_comments, (int)$indent);
      }
    } else {
      if ($parent_id === null) {
        $px = max(0, (int)$indent) * 20;
        echo "<div style='margin-left: {$px}px;'>Keine Kommentare vorhanden.</div>";
      }
    }
  }
}

/**
 * Rendert ein einzelnes Kommentar-Element inkl. Kinder (rekursiv).
 *
 * @param array $comment
 * @param array $child_comments  Map: parent_id => array<comment>
 * @param int   $indent
 * @return void
 */
if (!function_exists('display_comment_item')) {
  function display_comment_item($comment, $child_comments, $indent) {
    $indent = max(0, (int)$indent);
    $px     = $indent * 20;

    $username  = htmlspecialchars((string)($comment['username'] ?? 'User'), ENT_QUOTES, 'UTF-8');
    $createdAt = isset($comment['created_at']) ? (string)$comment['created_at'] : '';
    $dateStr   = $createdAt !== '' ? date('F j, Y, g:i a', strtotime($createdAt)) : '';
    $content   = htmlspecialchars((string)($comment['comment'] ?? ''), ENT_QUOTES, 'UTF-8');
    $id        = (int)($comment['id'] ?? 0);
    ?>
    <div class="comment-item"
         style="margin-left: <?php echo $px; ?>px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; background-color: #f9f9f9;">
      <div class="comment-header"
           style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div style="font-weight: bold; color: #333;"><?php echo $username; ?></div>
        <div style="font-size: 0.85em; color: #777;"><?php echo htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>

      <div class="comment-body" style="margin-bottom: 10px; color: #444; line-height: 1.6;">
        <?php echo nl2br($content); ?>
      </div>

      <div class="comment-footer" style="margin-top: 10px;">
        <a href="#"
           class="reply-link"
           data-id="<?php echo $id; ?>"
           style="color: #007BFF; text-decoration: none;">Antworten</a>
      </div>
    </div>
    <?php
    if (isset($child_comments[$id]) && is_array($child_comments[$id])) {
      foreach ($child_comments[$id] as $child_comment) {
        display_comment_item($child_comment, $child_comments, $indent + 2);
      }
    }
  }
}
