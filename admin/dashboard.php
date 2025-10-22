<?php
/**
 * Datei: admin/dashboard.php
 * Zweck: Admin-Dashboard (Übersichtsseite, lädt Kennzahlen via Controller)
 *
 * Hinweise:
 * - Zugriff nur für Admins (adminOnly()).
 * - $vm enthält die von AdminDashboardController::stats() gelieferten Kennzahlen.
 * - Darstellung ist aktuell minimal; für Kacheln/Charts kann leicht erweitert werden.
 */

require __DIR__ . '/_admin_boot.php';
usersOnly();

require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminDashboardController;
use App\OOP\Repositories\DbRepository;

// ViewModel (Kennzahlen) laden
$vm = (new AdminDashboardController(new DbRepository()))->stats() ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../assets/css/style.css" />
  <!-- Admin-Styles -->
  <link rel="stylesheet" href="../assets/css/admin.css" />

  <title>Admin Section - Dashboard</title>

  <!-- Favicons / Manifest -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png'; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png'; ?>">
  <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest'; ?>">
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
      <div class="content">
        <h2 class="page-title">Dashboard</h2>

        <!-- System-/Flash-Meldungen -->
        <?php include ROOT_PATH . '/app/includes/messages.php'; ?>
        <?php?>
      </div>
    </div>
  </div>

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor (hier nicht zwingend nötig) -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../assets/js/scripts.js"></script>
</body>
</html>
