<?php
/**
 * Datei: admin/topics/edit.php
 * Zweck: Admin-Ansicht zum Bearbeiten eines bestehenden Topics
 *
 * Hinweise:
 * - Zugriff ausschließlich für Admins (adminOnly()).
 * - Controller kapselt das Laden, Validieren und Speichern.
 * - Formular befüllt bei Fehlern die zuletzt eingegebenen Werte (Value-Persistenz).
 */

require __DIR__ . '/../_admin_boot.php'; // Bootstrap (Session, Konstanten, Guards)
usersOnly();
                              // Zugriffsschutz: nur Admins
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';// OOP-Autoload/Bootstrap

use App\OOP\Controllers\Admin\AdminTopicController;
use App\OOP\Repositories\DbRepository;

// Controller instanzieren und Request verarbeiten
$ctrl = new AdminTopicController(new DbRepository());
$vm   = $ctrl->edit($_GET, $_POST);

// ViewModel entpacken (mit Defaults)
$errors      = $vm['errors']      ?? [];
$id          = $vm['id']          ?? '';
$name        = $vm['name']        ?? '';
$description = $vm['description'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Font Awesome (optional, falls Icons genutzt werden) -->
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <!-- Google Fonts (legacy) -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <!-- Admin-Styles -->
  <link rel="stylesheet" href="../../assets/css/admin.css" />

  <title>Admin Section – Edit Topic</title>
</head>
<body>
  <!-- Oberer Admin-Header -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <!-- Seiten-Wrapper -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <!-- Hauptinhalt -->
    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Topic</a>
        <a href="index.php"  class="btn btn-big">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Edit Topic</h2>

        <!-- Validierungsfehler (einheitliches Partial) -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Bearbeiten eines Topics
          - action zeigt auf edit.php (dieselbe Seite, Controller verarbeitet Post)
          - Hinweis: Für Produktion CSRF-Token ergänzen
        -->
        <form action="edit.php" method="post">
          <!-- Topic-ID (hidden) -->
          <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

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
            <button type="submit" name="update-topic" class="btn btn-big">Update Topic</button>
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
