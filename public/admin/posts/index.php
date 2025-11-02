<?php
// Zweck: Admin – Listenansicht & Verwaltung aller Blog-Posts inkl. Moderationsspalte

declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php';            // Session/ROOT_PATH/BASE_URL
adminOnly();                                        // nur Admins

require_once ROOT_PATH . '/app/Support/includes/bootstrap.php'; // Autoload/DB
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';       // CSRF-Helper
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminPostController.php';

use App\Http\Controllers\Admin\AdminPostController;
use App\Infrastructure\Repositories\DbRepository;

$isAdmin = !empty($_SESSION['admin'] ?? 0);         // Flag für Moderationsspalte

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
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">

  <title>Admin – Manage Posts</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="<?= BASE_URL ?>/public/admin/posts/create.php" class="btn btn-big">Add Post</a>
        <a href="<?= BASE_URL ?>/public/admin/posts/index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Posts</h2>

        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

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
            <?php require ROOT_PATH . "/public/admin/posts/displayPosts.php"; // rendert nur <tr>…</tr> ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/resources/assets/js/scripts.js"></script>
</body>
</html>
