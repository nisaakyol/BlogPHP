<?php
/**
 * Datei: admin/posts/edit.php
 * Zweck: Admin-Ansicht zum Bearbeiten eines bestehenden Blog-Posts
 *
 * Hinweise:
 * - Zugriff nur für eingeloggte User (usersOnly()).
 * - Admins können direkt veröffentlichen; normale User setzen ein Freigabe-Flag.
 * - Controller kapselt die Verarbeitung (Lesen, Validieren, Speichern).
 */

require __DIR__ . '/../_admin_boot.php'; // Bootstrap (Session, Konstanten, Guards)
usersOnly();                              // Zugriffsschutz

require_once ROOT_PATH . '/app/OOP/bootstrap.php'; // Autoload/Bootstrap für OOP-Teil

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

/**
 * Request an Controller übergeben.
 * handleEdit($_GET, $_POST, $_FILES) liefert ein ViewModel (Array) mit u. a.:
 * - errors[]   : Validierungsfehler
 * - id, title, body, topic_id, published
 * - topics[]   : Für das <select> der Kategorien
 */
$ctrl = new AdminPostController(new DbRepository());
$vm   = $ctrl->handleEdit($_GET, $_POST, $_FILES);

// ViewModel entpacken (mit Defaults)
$errors    = $vm['errors']    ?? [];
$id        = $vm['id']        ?? '';
$title     = $vm['title']     ?? '';
$body      = $vm['body']      ?? '';
$topic_id  = $vm['topic_id']  ?? '';
$published = $vm['published'] ?? '';
$topics    = $vm['topics']    ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <!-- Basis-Styles: öffentlich + Admin -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <title>Admin Section – Edit Post</title>
</head>
<body>
  <!-- Fester Admin-Header -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <!-- Linke Admin-Sidebar -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Edit Posts</h2>

        <!-- Validierungsfehler -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Bearbeiten des Posts
          - enctype="multipart/form-data" für optionalen Bild-Upload
          - id wird als Hidden-Field mitgegeben
          - Hinweis: Für Produktion CSRF-Token ergänzen
        -->
        <form action="edit.php" method="post" enctype="multipart/form-data">
          <!-- Post-ID (hidden) -->
          <input
            type="hidden"
            name="id"
            value="<?php echo (int)$id; ?>"
          >

          <!-- Titel -->
          <div>
            <label for="title">Title</label>
            <input
              type="text"
              id="title"
              name="title"
              class="text-input"
              value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>

          <!-- Inhalt -->
          <div>
            <label for="body">Body</label>
            <textarea
              name="body"
              id="body"
            ><?php echo htmlspecialchars($body, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <!-- Bild-Upload (optional: neues Titelbild) -->
          <div>
            <label for="image">Image</label>
            <input
              type="file"
              id="image"
              name="image"
              class="text-input"
            >
          </div>

          <!-- Topic-Auswahl -->
          <div>
            <label for="topic_id">Topic</label>
            <select
              id="topic_id"
              name="topic_id"
              class="text-input"
            >
              <option value=""></option>
              <?php foreach ($topics as $topic): ?>
                <?php
                  $optId   = (int)($topic['id'] ?? 0);
                  $optName = (string)($topic['name'] ?? '');
                  $selected = (!empty($topic_id) && (int)$topic_id === $optId) ? 'selected' : '';
                ?>
                <option value="<?php echo $optId; ?>" <?php echo $selected; ?>>
                  <?php echo htmlspecialchars($optName, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Publish-Option (Admin direkt, sonst Freigabe) -->
          <div>
            <?php if (!empty($_SESSION['admin'])): ?>
              <label>
                <input
                  type="checkbox"
                  name="published"
                  <?php echo !empty($published) ? 'checked' : ''; ?>
                >
                Publish
              </label>
            <?php else: ?>
              <label>
                <input
                  type="checkbox"
                  name="AdminPublish"
                  <?php echo !empty($published) ? 'checked' : ''; ?>
                >
                Zum Publishen an Admin senden
              </label>
            <?php endif; ?>
          </div>

          <!-- Absenden -->
          <div>
            <button type="submit" name="update-post" class="btn btn-big">Update Post</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
