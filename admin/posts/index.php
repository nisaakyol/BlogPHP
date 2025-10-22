<?php
/**
 * Datei: admin/posts/index.php
 * Zweck: Übersicht zur Verwaltung aller Blog-Posts (Listenansicht + Aktionen)
 *
 * - Eingeloggt erforderlich (usersOnly)
 * - Admin: Moderation (Approve/Reject)
 * - Normale User: nur eigene Posts (Edit/Delete)
 * - displayPosts.php rendert NUR <tr>…</tr>-Zeilen (keine zweite Tabelle)
 */

require __DIR__ . '/../_admin_boot.php';
usersOnly();

require_once ROOT_PATH . '/app/includes/bootstrap_once.php';
require_once ROOT_PATH . '/app/helpers/csrf.php';

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

$isAdmin = !empty($_SESSION['admin']);

// Controller
$ctrl = new AdminPostController(new DbRepository());

// (Optional) Alte GET-Aktionen – wenn du sie noch brauchst. Sonst entfernen.
// if (isset($_GET['delete_id'])) { $ctrl->delete((int)$_GET['delete_id']); }
// if (isset($_GET['published'], $_GET['p_id'])) { $ctrl->togglePublish((int)$_GET['p_id'], (int)$_GET['published']); }

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

  <!-- Fonts/Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">

  <title>Admin – Manage Posts</title>
</head>
<body>
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Posts</h2>

        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <table class="table" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="width:60px;">SN</th>
              <th>Title</th>
              <th style="width:180px;">Author</th>
              <th style="width:130px;">Status</th>
              <th style="width:240px;">Actions</th>
              <?php if ($isAdmin): ?>
                <th style="width:260px;">Moderation</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php
              // displayPosts.php rendert nur die <tr>-Zeilen
              require ROOT_PATH . "/admin/posts/displayPosts.php";
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
