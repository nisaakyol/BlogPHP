<?php
require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminUserController.php';

use App\Http\Controllers\Admin\AdminUserController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminUserController(new DbRepository());

// Delete via GET (bestehendes Verhalten)
if (isset($_GET['delete_id'])) {
  $ctrl->delete((int) $_GET['delete_id']);
  header('Location: ' . BASE_URL . '/public/admin/users/index.php');
  exit;
}

// ViewModel laden
$vm          = $ctrl->index();
$admin_users = $vm['admin_users'] ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- Fonts / Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">

  <title>Manage Users</title>

  <style>
    /* ===== Global Background (Sand) ===== */
    body {
      margin: 0;
      background: #efe7dd !important;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif;
    }

    /* ===== Card Layout wie bei Posts & Topics ===== */
    .admin-content {
      padding: 0 24px 40px;
    }

    .admin-content .content {
      background: #ffffff;
      border-radius: 18px;
      padding: 26px 28px 32px;
      max-width: 1000px;
      margin: 0 auto;
      box-shadow: 0 18px 55px rgba(0,0,0,.08);
      border: 1px solid rgba(0,0,0,.03);
    }

    .page-title {
      text-align: center;
      margin-top: 0;
      font-size: 1.7rem;
      font-weight: 700;
      color: #2e3a46;
      margin-bottom: 1.5rem;
    }

    /* ===== Buttons ===== */
    .button-group {
      display: flex;
      justify-content: center;
      gap: 14px;
      margin-bottom: 24px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 0.55rem 1.2rem;
      border-radius: 999px;
      text-decoration: none;
      border: 1px solid transparent;
      font-size: 0.95rem;
      cursor: pointer;
      transition: .2s;
    }
    .btn i { font-size: .9em; }

    .btn--primary {
      background: #2e3a46;
      color: #efe7dd;
    }
    .btn--primary:hover { background: #1f2831; }

    .btn--ghost {
      background: transparent;
      color: #2e3a46;
      border: 1px solid #2e3a46;
    }
    .btn--ghost:hover {
      background: rgba(46,58,70,0.09);
    }

    .btn--sm {
      padding: 0.35rem 0.8rem;
      font-size: 0.85rem;
    }
    .btn--success {
      background: #1b9a5b;
      color: #fff;
    }
    .btn--success:hover { background: #15834c; }

    .btn--danger {
      background: #c0392b;
      color: #fff;
    }
    .btn--danger:hover { background: #a93226; }

    /* ===== Table (identisch mit Posts/Topics) ===== */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
      font-size: .95rem;
    }

    thead tr {
      background: #e9dfcf;
      color: #2e3a46;
    }

    th, td {
      padding: 12px 14px;
      border-bottom: 1px solid #f2f2f2;
      text-align: left;
    }

    tbody tr:nth-child(even) { background: #faf6ee; }
    tbody tr:hover { background: #f3ede3; }

    th:first-child,
    td:first-child { width: 60px; text-align: center; }

    .table-actions {
      display: flex;
      gap: 6px;
      white-space: nowrap;
    }

        /* ==== Action-Chips wie bei Manage Topics ==== */
    .btn-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 0.28rem 0.85rem;
      border-radius: 999px;
      font-size: 0.85rem;
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

    .btn-chip--edit {
      background: #e5e7eb;
      color: #111827;
    }

    .btn-chip--edit:hover {
      background: #d1d5db;
    }

    .btn-chip--delete {
      background: #fee2e2;
      color: #b91c1c;
    }

    .btn-chip--delete:hover {
      background: #fecaca;
    }

    .btn-chip:active {
      transform: translateY(1px);
    }

  </style>

</head>
<body>

<?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

<div class="admin-wrapper">
  <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

  <div class="admin-content">

    <!-- Buttons -->
    <div class="button-group">
      <a href="create.php" class="btn btn--primary">
        <i class="fas fa-user-plus"></i> Add User
      </a>
      <a href="index.php" class="btn btn--ghost">
        <i class="fas fa-users-cog"></i> Manage Users
      </a>
    </div>

    <!-- Card -->
    <div class="content">
      <h2 class="page-title">Manage Users</h2>

      <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

      <!-- Tabelle mit allen Admin-Usern -->
      <table>
        <!-- Tabellenkopf: Spalten für Benutzerübersicht -->
        <thead>
          <tr>
            <th>SN</th>
            <th>Username</th>
            <th>Email</th>
            <th colspan="2">Action</th>
          </tr>
        </thead>
        <tbody>

        <!-- Fallback: Keine Benutzer gefunden -->
        <?php if (empty($admin_users)): ?>
          <tr><td colspan="4">Keine Benutzer vorhanden.</td></tr>
        <?php endif; ?>

        <!-- Alle Benutzer auflisten (Nummer, Name, Email, Aktionen -->
        <?php foreach ($admin_users as $i => $u): ?>
          <?php
          // Daten des aktuellen Benutzers vorbereiten
            $id = (int)$u['id'];
            $name = htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8');
          ?>
          <!-- Einzelne Benutzerzeile -->
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= $name ?></td>
            <td><?= $email ?></td>
            <!-- Aktionen für diesen Benutzer: Bearbeiten oder Löschen -->
            <td class="table-actions" colspan="2">
              <!-- Benutzer bearbeiten -->
  <a href="edit.php?id=<?= $id ?>" class="btn-chip btn-chip--edit">
    Edit
  </a>
<!-- Benutzer löschen (mit Bestätigung) -->
  <a href="index.php?delete_id=<?= $id ?>"
     onclick="return confirm('User wirklich löschen?');"
     class="btn-chip btn-chip--delete">
    Delete
  </a>
</td>
          </tr>
        <?php endforeach; ?>

        </tbody>
      </table>

    </div>
  </div>
</div>

<!-- Admin-JavaScript laden (Tabellen- und UI-Funktionen) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>

</body>
</html>
