<?php
/**
 * Datei: admin/topics/create.php
 * Zweck: Admin-Ansicht zum Anlegen eines neuen Topics (Kategorie/Tag)
 *
 * Hinweise:
 * - Zugriff ausschließlich für Admins (adminOnly()).
 * - POST wird an AdminTopicController::store() übergeben (validiert & speichert).
 * - GET lädt AdminTopicController::create() für die Formularansicht.
 * - Bei Fehlern werden Messages/Errors via Session angezeigt und
 *   die zuletzt eingegebenen Werte wieder befüllt.
 * - Für Produktion: CSRF-Token ergänzen.
 */

require __DIR__ . '/../_admin_boot.php';   // Session, ROOT_PATH, BASE_URL, Guards
adminOnly();                               // nur Admins

require_once ROOT_PATH . '/app/OOP/bootstrap.php';  // Autoloader

use App\OOP\Controllers\Admin\AdminTopicController;
use App\OOP\Repositories\DbRepository;

$ctrl = new AdminTopicController(new DbRepository());

// POST → speichern (Controller macht Redirect bei Erfolg/Fehler)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // erwartet: $_POST['name'], $_POST['description']
    $ctrl->store($_POST);
    exit; // safety
}

// GET → Formular anzeigen
$vm         = $ctrl->create();
$topic      = $vm['topic']  ?? ['name' => '', 'description' => ''];
$errors     = $_SESSION['errors'] ?? $vm['errors'] ?? [];
$old        = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']); // einmalig anzeigen, dann leeren
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">

  <title>Admin Section – Add Topic</title>
</head>
<body>
  <!-- Fester Admin-Header -->
  <?php include ROOT_PATH . "/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <!-- Linke Admin-Sidebar -->
    <?php include ROOT_PATH . "/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="index.php"  class="btn btn-big btn--ghost">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Add Topic</h2>

        <!-- System-/Flash-Meldungen -->
        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <!-- Validierungsfehler -->
        <?php if (!empty($errors)): ?>
          <div class="msg error">
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Formular -->
        <form action="create.php" method="post">
          <div class="input-group">
            <label for="name">Name *</label>
            <input
              type="text"
              id="name"
              name="name"
              class="text-input"
              required
              value="<?= htmlspecialchars($old['name'] ?? $topic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
          </div>

          <div class="input-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              name="description"
              rows="5"
              class="text-input"
            ><?= htmlspecialchars($old['description'] ?? $topic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="input-group">
            <button type="submit" class="btn btn-big btn--primary">Save</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Optional: CKEditor, falls benötigt
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
