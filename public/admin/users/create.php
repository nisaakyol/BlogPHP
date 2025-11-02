<?php
declare(strict_types=1);

// Admin: neuen Benutzer anlegen (eigene POST-Verarbeitung)
require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/Support/helpers/csrf.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';

use App\Infrastructure\Repositories\DbRepository;

$errors = [];
$username = $email = '';
$adminFlag = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    csrf_validate_or_die($_POST['csrf_token'] ?? ''); // CSRF prüfen

    // Felder
    $username     = trim((string)($_POST['username'] ?? ''));
    $email        = trim((string)($_POST['email'] ?? ''));
    $password     = (string)($_POST['password'] ?? '');
    $passwordConf = (string)($_POST['passwordConf'] ?? '');
    $adminFlag    = isset($_POST['admin']) ? 1 : 0;

    // Validierung
    if ($username === '' || mb_strlen($username) < 3) $errors[] = 'Username ist zu kurz.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ungültige E-Mail.';
    if ($password === '' || mb_strlen($password) < 6) $errors[] = 'Passwort ist zu kurz (min. 6 Zeichen).';
    if ($password !== $passwordConf) $errors[] = 'Passwörter stimmen nicht überein.';

    // Duplikate?
    if (!$errors) {
        $repo = new DbRepository();
        $dup  = $repo->findUserByUsernameOrEmail($username, $email);
        if ($dup) $errors[] = 'Username oder E-Mail existieren bereits.';
    }

    // Anlegen
    if (!$errors) {
        $repo = $repo ?? new DbRepository();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $uid = $repo->createUser($username, $email, $hash); // legt user an (admin=0)

        if ($adminFlag === 1) {
            // admin-Flag setzen, wenn Spalte existiert
            $pdo = \App\Infrastructure\Core\DB::pdo();
            $hasAdmin = (bool)$pdo->query("
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='admin' LIMIT 1
            ")->fetchColumn();
            if ($hasAdmin) {
                $st = $pdo->prepare("UPDATE users SET admin = 1 WHERE id = :id");
                $st->execute([':id' => $uid]);
            }
        }

        $_SESSION['message'] = 'Benutzer erfolgreich angelegt.';
        $_SESSION['type']    = 'success';
        header('Location: ' . BASE_URL . '/public/admin/users/index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css" />
  <title>Admin Section - Add User</title>
</head>
<body>
  <?php include ROOT_PATH . '/public/admin/adminHeader.php'; ?>
  <div class="admin-wrapper">
    <?php include ROOT_PATH . '/public/admin/adminSidebar.php'; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">Add User</a>
        <a href="index.php"  class="btn btn--lg btn--ghost">Manage Users</a>
      </div>

      <div class="content">
        <h2 class="page-title">Add User</h2>

        <?php if ($errors): ?>
          <div class="msg error"><ul><?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form action="create.php" method="post" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="input-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" class="text-input"
                   value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <div class="input-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" class="text-input"
                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="text-input" required>
          </div>

          <div class="input-group">
            <label for="passwordConf">Password Confirmation</label>
            <input id="passwordConf" name="passwordConf" type="password" class="text-input" required>
          </div>

          <div class="input-group">
            <label class="checkbox">
              <input type="checkbox" name="admin" value="1" <?= $adminFlag ? 'checked' : '' ?>>
              <span>Als Admin markieren</span>
            </label>
          </div>

          <div class="input-group">
            <button type="submit" name="create_user" class="btn btn--primary">Create</button>
            <a href="index.php" class="btn btn--ghost">Abbrechen</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
