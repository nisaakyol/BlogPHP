<?php
/**
 * Datei: admin/users/edit.php
 * Zweck: Admin-Ansicht zum Bearbeiten eines Benutzers (mit vorbefüllten Feldern)
 */

require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminUserController;
use App\OOP\Repositories\DbRepository;

$ctrl = new AdminUserController(new DbRepository());

// ID aus Query
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['message'] = 'Ungültige Benutzer-ID.';
    $_SESSION['type']    = 'error';
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

// POST → update (Controller validiert/speichert/redirectet)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->update($id, $_POST);
    exit;
}

// GET → Daten laden
$vm     = $ctrl->edit($id);  // sollte ['user'=>['username'=>..., 'email'=>..., 'admin'=>0/1], 'errors'=>[]] liefern
$user   = $vm['user']   ?? ['username'=>'','email'=>'','admin'=>0];
$errors = $_SESSION['errors'] ?? $vm['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

// Helper zum Belegen (old > user)
$val = function(string $key, $default = '') use ($old, $user) {
    if (array_key_exists($key, $old))   return (string)$old[$key];
    if (array_key_exists($key, $user))  return (string)$user[$key];
    return (string)$default;
};
$adminChecked = (int)($old['admin'] ?? $user['admin'] ?? 0) === 1;
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <title>Admin Section - Edit User</title>
</head>
<body>
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">Add User</a>
        <a href="index.php"  class="btn btn--lg btn--ghost">Manage Users</a>
      </div>

      <div class="content">
        <h2 class="page-title">Edit User</h2>

        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <?php if (!empty($errors)): ?>
          <div class="msg error">
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="edit.php?id=<?= (int)$id ?>">
          <div class="input-group">
            <label for="username">Username *</label>
            <input id="username" name="username" type="text" class="text-input" required
                   value="<?= htmlspecialchars($val('username'), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="input-group">
            <label for="email">Email *</label>
            <input id="email" name="email" type="email" class="text-input" required
                   value="<?= htmlspecialchars($val('email'), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="input-group">
            <label for="password">Neues Passwort (optional)</label>
            <input id="password" name="password" type="password" class="text-input" autocomplete="new-password">
          </div>

          <div class="input-group">
            <label for="passwordConf">Passwort wiederholen</label>
            <input id="passwordConf" name="passwordConf" type="password" class="text-input" autocomplete="new-password">
          </div>

          <div class="input-group">
            <label class="checkbox">
              <input type="checkbox" name="admin" value="1" <?= $adminChecked ? 'checked' : '' ?>>
              Admin
            </label>
          </div>

          <div class="input-group">
            <button type="submit" class="btn btn--primary">Update User</button>
            <a href="index.php" class="btn btn--ghost">Abbrechen</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
