<?php
require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminUserController.php';

use App\Http\Controllers\Admin\AdminUserController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminUserController(new DbRepository()); // Controller

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    crossorigin="anonymous"
  />
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">

  <title>Admin Section - Manage Users</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
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

        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

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
                  <td><?= $idx + 1 ?></td>
                  <td><?= htmlspecialchars($uname, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($uemail, ENT_QUOTES, 'UTF-8') ?></td>
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
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
