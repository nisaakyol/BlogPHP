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

  <style>
    /* ----------------------------------------------
       Hintergrund & Grundlayout (Sand-Ton)
    ---------------------------------------------- */
    html,
    body {
      background: #efe7dd !important; /* Sand */
      margin: 0;
      padding: 0;
    }

    .admin-wrapper {
      display: flex;
      min-height: calc(100vh - 66px); /* unterhalb des Headers */
      background: #efe7dd;
    }

    .admin-content {
      flex: 1;
      padding: 40px 50px;
      box-sizing: border-box;
      background: transparent;
    }

    /* Sidebar bleibt links, aber ohne Lila-Feeling */
    .admin-sidebar {
      background: #d1d2d2; /* dezentes Grau */
    }

    /* ----------------------------------------------
       Dashboard-Card
    ---------------------------------------------- */
    .admin-content .content {
      max-width: 1100px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 22px;
      padding: 28px 32px 32px;
      box-shadow: 0 18px 45px rgba(0,0,0,.10);
      border: 1px solid rgba(0,0,0,.03);
    }

    .page-title {
      font-size: 2rem;
      margin: 0 0 1rem;
      text-align: left;
    }

    .dashboard-subtitle {
      margin: 0 0 1.8rem;
      color: #555;
      font-size: .98rem;
    }

    /* Falls du später Kennzahlen anzeigen willst */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
      margin-bottom: 1.5rem;
    }

    .dashboard-card {
      background: #f9f5f0;
      border-radius: 18px;
      padding: 16px 18px;
      box-shadow: 0 10px 25px rgba(0,0,0,.04);
      border: 1px solid rgba(0,0,0,.03);
      display: flex;
      flex-direction: column;
      gap: .25rem;
    }

    .dashboard-card-title {
      font-size: .95rem;
      text-transform: uppercase;
      letter-spacing: .04em;
      color: #666;
    }

    .dashboard-card-value {
      font-size: 1.4rem;
      font-weight: 700;
      color: #222;
    }

    .dashboard-card-note {
      font-size: .85rem;
      color: #777;
    }

    /* Debug-Block für $vm nur dezent anzeigen */
    .dashboard-debug {
      margin-top: 1.5rem;
      font-size: .85rem;
      color: #777;
      background: #fafafa;
      border-radius: 12px;
      padding: 10px 12px;
      border: 1px dashed #ddd;
      overflow-x: auto;
    }

    @media (max-width: 900px) {
      .admin-content {
        padding: 20px 16px 30px;
      }
      .admin-content .content {
        padding: 20px 18px 24px;
      }
    }
  </style>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="content">
        <h2 class="page-title">Dashboard</h2>
        <p class="dashboard-subtitle">
          Überblick über deine Admin-Sektion. Hier kannst du Posts, Topics und Nutzer verwalten.
        </p>

        <?php include ROOT_PATH . '/app/Support/includes/messages.php'; ?>

        <?php if (!empty($vm)): ?>
          <div class="dashboard-grid">
            <?php if (isset($vm['posts_count'])): ?>
              <div class="dashboard-card">
                <span class="dashboard-card-title">Beiträge</span>
                <span class="dashboard-card-value"><?= (int)$vm['posts_count']; ?></span>
                <span class="dashboard-card-note">veröffentlichte Posts gesamt</span>
              </div>
            <?php endif; ?>

            <?php if (isset($vm['topics_count'])): ?>
              <div class="dashboard-card">
                <span class="dashboard-card-title">Themen</span>
                <span class="dashboard-card-value"><?= (int)$vm['topics_count']; ?></span>
                <span class="dashboard-card-note">aktive Themen im Blog</span>
              </div>
            <?php endif; ?>

            <?php if (isset($vm['users_count'])): ?>
              <div class="dashboard-card">
                <span class="dashboard-card-title">Nutzer</span>
                <span class="dashboard-card-value"><?= (int)$vm['users_count']; ?></span>
                <span class="dashboard-card-note">registrierte Benutzer</span>
              </div>
            <?php endif; ?>
          </div>

          <div class="dashboard-debug">
            <strong>Rohdaten (vm):</strong>
            <pre><?php print_r($vm); ?></pre>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
