<?php
// public/admin/dashboard.php
declare(strict_types=1);

// Pfade/Bootstrap
require __DIR__ . '/../path.php';
require ROOT_PATH . '/public/admin/_admin_boot.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
usersOnly();

// optionaler App-Bootstrap (falls nicht geladen)
$__bootstrap = ROOT_PATH . '/app/bootstrap.php';
if (is_file($__bootstrap) && !defined('TOP_BOOTSTRAP_LOADED')) {
    require_once $__bootstrap;
}

// Controller/Repo ermitteln
use App\Http\Controllers\Admin\AdminDashboardController;

$repoClass = null;
if (class_exists(\App\Infrastructure\Repositories\DbRepository::class)) {
    $repoClass = \App\Infrastructure\Repositories\DbRepository::class;
} elseif (class_exists(\App\Infrastructure\Repositories\DbRepository::class)) {
    $repoClass = \App\Infrastructure\Repositories\DbRepository::class;
} elseif (class_exists(\App\Http\Repositories\DbRepository::class)) {
    $repoClass = \App\Http\Repositories\DbRepository::class;
}

$repo = $repoClass ? new $repoClass() : null;

// Guards
usersOnly();
adminOnly();

// ViewModel
$vm = [];
if ($repo) {
    $ctrl = new AdminDashboardController($repo);
    $vm = $ctrl->stats() ?? [];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css" />
  <title>Admin Section - Dashboard</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="content">
        <h2 class="page-title">Dashboard</h2>
        <?php include ROOT_PATH . '/app/Support/includes/messages.php'; ?>
        <pre><?php /* var_dump($vm); */ ?></pre>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
