<?php
/**
 * Datei: admin/posts/create.php
 * Zweck: Admin-Ansicht zum Anlegen eines neuen Blog-Posts (Formular + Verarbeitung über Controller)
 *
 * Hinweise:
 * - Zugriff nur für eingeloggte User (usersOnly()) – Admins dürfen direkt veröffentlichen.
 * - Controller kapselt Business-Logik; View kümmert sich um Darstellung/Validierungsausgaben.
 * - Bei Formular-Reposts werden Eingaben wieder befüllt (Value-Persistenz).
 */

require __DIR__ . '/../_admin_boot.php'; // Bootstrap für Admin-Bereich (Session, Consts, Guards)
usersOnly();                              // Zugriffsschutz: nur eingeloggte Benutzer

require_once ROOT_PATH . '/app/OOP/bootstrap.php'; // Autoloader/DI etc. für OOP-Teil

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

/**
 * Controller aufbauen und Request verarbeiten.
 * handleCreate() gibt ein ViewModel (Array) zurück mit Keys:
 * - errors[]: Validierungsfehler
 * - title, body, topic_id, published: zuletzt gepostete Werte (Re-Rendering)
 * - topics[]: Liste der verfügbaren Topics für das <select>
 */
$ctrl = new AdminPostController(new DbRepository());
$vm   = $ctrl->handleCreate($_POST, $_FILES);

// ViewModel entpacken (mit Defaults)
$errors    = $vm['errors']    ?? [];
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
  <!-- Basis-Styles: öffentliches Styling + Admin-spezifisches Styling -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <title>Admin Section – Add Post</title>
</head>
<body>
  <!-- Fester Admin-Header (Navi, Usermenü) -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <!-- Linke Admin-Sidebar (Navigation) -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <div class="admin-content">
      <!-- Schnellzugriff: Post anlegen / verwalten -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php" class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Create Posts</h2>

        <!-- Validierungsfehler (einheitliches Partial) -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Erstellen eines Posts
          - enctype="multipart/form-data" für Bild-Upload
          - action zeigt auf create.php (dieselbe Seite, PRG-Pattern wird im Controller gehandhabt)
          - Hinweis: Für echte Produktivnutzung CSRF-Token ergänzen
        -->
        <form action="create.php" method="post" enctype="multipart/form-data">
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

          <!-- Body / Inhalt -->
          <div>
            <label for="body">Body</label>
            <textarea
              name="body"
              id="body"
            ><?php echo htmlspecialchars($body, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <!-- Bild-Upload -->
          <div>
            <label for="image">Image</label>
            <input type="file" id="image" name="image" class="text-input">
          </div>

          <!-- Topic-Auswahl -->
          <div>
            <label for="topic_id">Topic</label>
            <select id="topic_id" name="topic_id" class="text-input">
              <option value=""></option>
              <?php foreach ($topics as $topic): ?>
                <option
                  value="<?php echo (int)$topic['id']; ?>"
                  <?php echo (!empty($topic_id) && (int)$topic_id === (int)$topic['id']) ? 'selected' : ''; ?>
                >
                  <?php echo htmlspecialchars($topic['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Publish-Option (Admin darf direkt veröffentlichen; sonst Flag für Freigabe) -->
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
            <button type="submit" name="add-post" class="btn btn-big">Add Post</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor (klassisch) -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
