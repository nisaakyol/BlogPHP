<?php
/**
 * Datei: admin/posts/index.php
 * Zweck: Übersicht zur Verwaltung aller Blog-Posts (Listenansicht + Aktionen)
 *
 * Hinweise:
 * - Aktionen (delete, publish/unpublish) werden aktuell per GET ausgelöst
 *   (siehe Links in displayPosts.php). Für Produktion empfehlenswert: POST + CSRF.
 * - $ctrl->index() liefert das ViewModel mit $posts und $usersById.
 */

require __DIR__ . '/../_admin_boot.php'; // Bootstrap (Session, Konstanten, Guards)
usersOnly();                              // Zugriffsschutz: nur eingeloggte Benutzer

require_once ROOT_PATH . '/app/OOP/bootstrap.php'; // OOP-Autoload/Bootstrap

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

// Controller instanzieren
$ctrl = new AdminPostController(new DbRepository());

// Primitive GET-Aktionen (wie bestehend):
if (isset($_GET['delete_id'])) {
  $ctrl->delete((int)$_GET['delete_id']);
}
if (isset($_GET['published'], $_GET['p_id'])) {
  $ctrl->togglePublish((int)$_GET['p_id'], (int)$_GET['published']);
}

// ViewModel abrufen
$vm        = $ctrl->index();
$posts     = $vm['posts']     ?? [];
$usersById = $vm['usersById'] ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Font Awesome (Icons) -->
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  >

  <!-- Google Fonts (legacy – wird global evtl. ersetzt durch travel.css Fonts) -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <!-- Admin-Styles -->
  <link rel="stylesheet" href="../../assets/css/admin.css">

  <title>Admin Section – Manage Posts</title>
</head>
<body>
  <!-- Obere Admin-Navigation -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <!-- Linke Sidebar (Navigation im Adminbereich) -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <!-- Hauptinhalt: Postverwaltung -->
    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Posts</h2>

        <!-- System-/Flash-Meldungen -->
        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <!-- Tabellarische Übersicht -->
        <table>
          <thead>
            <tr>
              <th>SN</th>
              <th>Title</th>
              <th>Author</th>
              <th colspan="3">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
              /**
               * Row-Rendering ausgelagert:
               * - Admins: alle Posts inkl. publish/unpublish
               * - User : nur eigene Posts (kein publish/unpublish)
               * displayPosts.php nutzt $posts und $usersById.
               */
              require ROOT_PATH . "/admin/posts/displayPosts.php";
            ?>
          </tbody>
        </table>
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
