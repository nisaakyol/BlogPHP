<?php
/**
 * Datei: admin/topics/create.php
 * Zweck: Admin-Ansicht zum Anlegen eines neuen Topics (Kategorie/Tag)
 *
 * Hinweise:
 * - Zugriff ausschließlich für Admins (adminOnly()).
 * - Controller kapselt Validierung/Speichern und liefert ein ViewModel ($vm).
 * - Formular befüllt bei Fehlern die zuletzt eingegebenen Werte (Value-Persistenz).
 * - Empfehlung für Produktion: CSRF-Token ergänzen.
 */

require __DIR__ . '/../_admin_boot.php'; // Bootstrap (Session, Konstanten, Guards)
usersOnly();
                               // Zugriffsschutz: nur Admins

require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
 // OOP-Autoload/Bootstrap

use App\OOP\Controllers\Admin\AdminTopicController;
use App\OOP\Repositories\DbRepository;

// Controller aufbauen und Request verarbeiten
$ctrl = new AdminTopicController(new DbRepository());
$vm   = $ctrl->create($_POST);

// ViewModel entpacken (mit Defaults)
$errors      = $vm['errors']      ?? [];
$name        = $vm['name']        ?? '';
$description = $vm['description'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <title>Admin Section – Add Topic</title>
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
        <a href="create.php" class="btn btn-big">Add Topic</a>
        <a href="index.php"  class="btn btn-big">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Add Topic</h2>

        <!-- Validierungsfehler (einheitliches Partial) -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Anlegen eines Topics
          - action zeigt auf create.php (dieselbe Seite, Controller verarbeitet Post)
          - Hinweis: Für Produktion CSRF-Token ergänzen
        -->
        <form action="create.php" method="post">
          <!-- Name -->
          <div>
            <label for="name">Name</label>
            <input
              type="text"
              id="name"
              name="name"
              class="text-input"
              value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>

          <!-- Beschreibung -->
          <div>
            <label for="body">Description</label>
            <textarea
              name="description"
              id="body"
            ><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <!-- Absenden -->
          <div>
            <button type="submit" name="add-topic" class="btn btn-big">Add Topic</button>
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