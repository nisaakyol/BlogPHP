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
adminOnly();

require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminUserController;
use App\OOP\Repositories\DbRepository;

// Controller instanzieren
$ctrl = new AdminUserController(new DbRepository());

// Delete via GET (bestehendes Verhalten beibehalten)
if (isset($_GET['delete_id'])) {
  $ctrl->delete((int) $_GET['delete_id']);
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
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <!-- Admin-Styles -->
  <link rel="stylesheet" href="../../assets/css/admin.css" />

  <title>Admin Section - Manage Users</title>
</head>
<body>
  <!-- Admin-Header -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <!-- Seiten-Wrapper -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <!-- Hauptinhalt -->
    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add User</a>
        <a href="index.php"  class="btn btn-big">Manage Users</a>
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
            <?php foreach ($admin_users as $idx => $user): ?>
              <?php
                $uid      = (int)   ($user['id']       ?? 0);
                $uname    = (string)($user['username'] ?? '');
                $uemail   = (string)($user['email']    ?? '');
              ?>
              <tr>
                <!-- Laufnummer (1-basiert) -->
                <td><?php echo $idx + 1; ?></td>

                <!-- Username -->
                <td><?php echo htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- E-Mail -->
                <td><?php echo htmlspecialchars($uemail, ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Aktionen: Edit/Delete -->
                <td>
                  <a href="edit.php?id=<?php echo $uid; ?>" class="edit">edit</a>
                </td>
                <td>
                  <a href="index.php?delete_id=<?php echo $uid; ?>" class="delete">delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor (hier meist nicht benötigt) -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
