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

  <style>
    /* ============================
       Grundlayout & Hintergrund
    ============================ */
    html,
    body {
      background: #efe7dd !important; /* Sand */
      margin: 0;
      padding: 0;
    }

    .admin-wrapper {
      background: #efe7dd;
      min-height: calc(100vh - 66px);
      display: flex;
    }

    .admin-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 20px 40px 40px;
    }

    .admin-content .content {
      width: 100%;
      max-width: 1200px;
      margin-top: 10px; /* Abstand zum Header kleiner */
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 18px 45px rgba(0, 0, 0, 0.08);
      padding: 24px 32px 32px;
    }

    .page-title {
      text-align: center;
      margin: 0 0 18px;
      font-size: 1.9rem;
    }

    /* ============================
       Button-Gruppe oben
    ============================ */
    .button-group {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: flex-start;
      margin-bottom: 18px;
    }

    .button-group .btn.btn-big {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 0.55rem 1.3rem;
      border-radius: 999px;
      font-size: 0.95rem;
      background: #d2cddc;
      border: 1px solid rgba(0,0,0,0.1);
      color: #f3f2f2ff !important;
      text-decoration: none;
    }

    .button-group .btn.btn-big i {
      font-size: 0.9rem;
    }

    .button-group .btn.btn-big:hover {
      background: #030410;
      color: #ffffff !important;
    }

    /* ============================
       Tabelle: Layout & Farben
    ============================ */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 0.95rem;
    }

    .table thead th {
      text-align: left;
      padding: 10px 12px;
      background: #f7f1e7;
      border-bottom: 1px solid #e0d6c7;
      font-weight: 600;
    }

    .table tbody tr:nth-child(even) {
      background: #fdfaf6;
    }

    .table tbody tr:nth-child(odd) {
      background: #ffffff;
    }

    .table tbody td {
      padding: 9px 12px;
      border-bottom: 1px solid #eee2d5;
      vertical-align: middle;
    }

    .col-sn {
      width: 50px;
      text-align: center;
    }

    .col-status {
      width: 110px;
    }

    .col-actions {
      width: 210px;
      white-space: nowrap;
    }

    .col-note {
      width: 160px;
    }

    /* Status-Badge */
    .badge-status {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 70px;
      padding: 0.15rem 0.6rem;
      border-radius: 999px;
      font-size: 0.8rem;
      border: 1px solid transparent;
    }

    .badge-status.draft {
      background: #fee2e2;
      border-color: #fecaca;
      color: #7f1d1d;
    }

    .badge-status.published {
      background: #dcfce7;
      border-color: #bbf7d0;
      color: #166534;
    }

    /* ============================
       Actions: View / Edit / Delete
    ============================ */

    /* generelles Styling für Links in der Actions-Spalte */
    .table td.col-actions a,
    .table td.actions a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 0.25rem 0.8rem;
      margin-right: 0.25rem;
      border-radius: 999px;
      font-size: 0.83rem;
      text-decoration: none;
      border: 1px solid transparent;
      cursor: pointer;
    }

    /* View-Button (hellblau) */
    .table td.col-actions a.view,
    .table td.actions a.view {
      background: #e0f2fe;
      border-color: #bfdbfe;
      color: #0f172a;
    }

    /* Edit-Button (grünlich) */
    .table td.col-actions a.edit,
    .table td.actions a.edit {
      background: #dcfce7;
      border-color: #bbf7d0;
      color: #064e3b;
    }

    /* Delete-Button (rot) */
    .table td.col-actions a.delete,
    .table td.actions a.delete {
      background: #fee2e2;
      border-color: #fecaca;
      color: #7f1d1d;
    }

    .table td.col-actions a.view:hover,
    .table td.actions a.view:hover {
      background: #bfdbfe;
    }

    .table td.col-actions a.edit:hover,
    .table td.actions a.edit:hover {
      background: #a7f3d0;
    }

    .table td.col-actions a.delete:hover,
    .table td.actions a.delete:hover {
      background: #fecaca;
    }

    /* Wenn du eigene Klassen wie .action-chip verwendest,
       kannst du sie zusätzlich hiermit abdecken */
    .action-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 0.25rem 0.8rem;
      border-radius: 999px;
      font-size: 0.83rem;
      border: 1px solid transparent;
      text-decoration: none;
      cursor: pointer;
      margin-right: 0.25rem;
    }
    .action-chip--view   { background:#e0f2fe; border-color:#bfdbfe; color:#0f172a; }
    .action-chip--edit   { background:#dcfce7; border-color:#bbf7d0; color:#064e3b; }
    .action-chip--delete { background:#fee2e2; border-color:#fecaca; color:#7f1d1d; }

    .action-chip--view:hover   { background:#bfdbfe; }
    .action-chip--edit:hover   { background:#a7f3d0; }
    .action-chip--delete:hover { background:#fecaca; }

    /* Moderationsspalte etwas dezenter */
    .col-note {
      font-size: 0.85rem;
      color: #4b5563;
    }

        /* ==== Chip-Buttons wie bei Manage Topics / Users ==== */
    .btn-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 0.28rem 0.85rem;
      border-radius: 999px;
      font-size: 0.83rem;
      border: none;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.15s ease, transform 0.05s ease;
      font-family: inherit;
      white-space: nowrap;
    }

    .btn-chip i {
      font-size: 0.8rem;
    }

    /* VIEW – neutral grau */
    .btn-chip--view {
      background:#e5e7eb;
      color:#111827;
    }
    .btn-chip--view:hover {
      background:#d1d5db;
    }

    /* EDIT – hellgrau */
    .btn-chip--edit {
      background:#e5e7eb;
      color:#111827;
    }
    .btn-chip--edit:hover {
      background:#d1d5db;
    }

    /* DELETE – rot */
    .btn-chip--delete {
      background:#fee2e2;
      color:#b91c1c;
    }
    .btn-chip--delete:hover {
      background:#fecaca;
    }

    @media (max-width: 900px) {
      .admin-content {
        padding: 16px 10px 30px;
      }
      .admin-content .content {
        padding: 18px 16px 24px;
      }
      .button-group {
        justify-content: center;
      }
      .page-title {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <!-- Globaler Admin-Header (Navigation, Benutzerinformationen) -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <!-- Haupt-Container für das Admin-Layout -->
  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="content">
        <div class="button-group">
          <a href="<?= BASE_URL ?>/public/admin/posts/create.php" class="btn btn-big">
            <i class="fa fa-plus"></i> Add Post
          </a>
          <a href="<?= BASE_URL ?>/public/admin/posts/index.php" class="btn btn-big">
            <i class="fa fa-list"></i> Manage Posts
          </a>
        </div>

        <h2 class="page-title">Manage Posts</h2>

        <!-- Systemmeldungen anzeigen (Erfolg, Fehler, Hinweise) -->
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
            <?php
              // rendert nur <tr>…</tr> – dort liegen auch die View/Edit/Delete-Links
              require ROOT_PATH . "/public/admin/posts/displayPosts.php";
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js?v=5"></script>
</body>
</html>
