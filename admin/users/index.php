<?php
/**
 * Datei: admin/users/index.php
 * Zweck: Übersicht/Verwaltung der Benutzer (Listenansicht, Edit/Delete)
 *
 * Hinweise:
 * - Delete wird aktuell per GET-Parameter ausgelöst (index.php?delete_id=...).
 *   Für Produktion empfehlenswert: POST + CSRF-Token.
 */

require __DIR__ . '/../_admin_boot.php';
adminOnly(); // oder usersOnly(), falls gewünscht

require_once ROOT_PATH . '/app/includes/bootstrap.php';

use App\OOP\Controllers\Admin\AdminUserController;
use App\OOP\Repositories\DbRepository;

// Controller instanzieren
$ctrl = new AdminUserController(new DbRepository());

// Delete via GET (bestehendes Verhalten beibehalten)
if (isset($_GET['delete_id'])) {
  $ctrl->delete((int) $_GET['delete_id']);
  // delete() sollte redirecten; sonst hier zur Sicherheit:
  header('Location: ' . BASE_URL . '/admin/users/index.php');
  exit;
}

// ViewModel laden
$vm          = $ctrl->index();
$admin_users = $vm['admin_users'] ?? [];
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
    crossorigin="anonymous"
  />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <!-- Admin-Styles (enthält .btn--sm/.btn--lg/.btn--primary/... und .table-actions) -->
  <link rel="stylesheet" href="../../assets/css/admin.css" />

  <title>Admin Section - Manage Users</title>
</head>
<body>
  <!-- Admin-Header -->
  <?php include ROOT_PATH . "/admin/adminHeader.php"; ?>

  <!-- Seiten-Wrapper -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar -->
    <?php include ROOT_PATH . "/admin/adminSidebar.php"; ?>

    <!-- Hauptinhalt -->
    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">
          <i class="fas fa-user-plus"></i> Add User
        </a>
        <a href="index.php" class="btn btn--lg btn--ghost">
          <i class="fas fa-users-cog"></i> Manage Users
        </a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Users</h2>

        <!-- System-/Flash-Meldungen -->
        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <!-- Benutzer-Tabelle -->
        <table>
          <thead>
            <tr>
              <th>SN</th>
              <th>Username</th>
              <th>Email</th>
              <th colspan="2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($admin_users)): ?>
              <tr>
                <td colspan="4">Keine Benutzer vorhanden.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($admin_users as $idx => $user): ?>
                <?php
                  $uid    = (int)   ($user['id']       ?? 0);
                  $uname  = (string)($user['username'] ?? '');
                  $uemail = (string)($user['email']    ?? '');
                ?>
                <tr>
                  <!-- Laufnummer (1-basiert) -->
                  <td><?= $idx + 1 ?></td>

                  <!-- Username -->
                  <td><?= htmlspecialchars($uname, ENT_QUOTES, 'UTF-8') ?></td>

                  <!-- E-Mail -->
                  <td><?= htmlspecialchars($uemail, ENT_QUOTES, 'UTF-8') ?></td>

                  <!-- Aktionen: Edit/Delete -->
                  <td class="table-actions">
                    <a href="edit.php?id=<?= $uid ?>" class="btn btn--sm btn--success">
                      <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="index.php?delete_id=<?= $uid ?>"
                       class="btn btn--sm btn--danger"
                       onclick="return confirm('User wirklich löschen?');">
                      <i class="fas fa-trash"></i> Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
