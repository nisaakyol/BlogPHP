<?php
/**
 * Datei: admin/posts/index.php
 * Zweck: Übersicht zur Verwaltung aller Blog-Posts (Listenansicht + Aktionen)
 *
 * - Admin: Moderation (Approve/Reject)
 * - displayPosts.php rendert NUR <tr>…</tr>-Zeilen (keine zweite Tabelle)
 */

declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/includes/bootstrap.php';
require_once ROOT_PATH . '/app/helpers/csrf.php';

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

$isAdmin = !empty($_SESSION['admin'] ?? 0);

// Controller
$ctrl = new AdminPostController(new DbRepository());

// Keine GET-Action mehr für kritische Dinge (Delete/Publish). Alles via POST + CSRF.
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
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">

  <title>Admin – Manage Posts</title>
</head>
<body>
  <?php include ROOT_PATH . "/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="<?= BASE_URL ?>/admin/posts/create.php" class="btn btn-big">Add Post</a>
        <a href="<?= BASE_URL ?>/admin/posts/index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Posts</h2>

        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <table class="table">
          <thead>
            <tr>
              <th class="col-sn">SN</th>
              <th>Title</th>
              <th>Author</th>
              <th class="col-status">Status</th>
              <th class="col-actions">Actions</th>
              <?php if ($isAdmin): ?>
                <th class="col-note">Moderation</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php require ROOT_PATH . "/admin/posts/displayPosts.php"; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/scripts.js"></script>
</body>
</html>
