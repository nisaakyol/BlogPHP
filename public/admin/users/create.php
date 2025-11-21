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
  <style>
    /* Hintergrund im Admin-Bereich (Sand) */
    html,
    body {
      background: #efe7dd !important;
      margin: 0;
      padding: 0;
    }

    .admin-wrapper {
      display: flex;
      min-height: calc(100vh - 66px);
      background: #efe7dd !important;
    }

    .admin-content {
      flex: 1;
      padding: 32px 40px 60px;
      box-sizing: border-box;
    }

    /* Weiße Karte wie bei Manage Users */
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

    /* Button-Gruppe oben: Add User / Manage Users */
    .button-group {
      display: flex;
      flex-direction: column;   /* untereinander */
      gap: 10px;
      margin: 20px 0 18px;
      align-items: flex-start;  /* linksbündig */
    }

    /* zweiten Button NICHT nach rechts schieben */
    .button-group .btn + .btn {
      margin-left: 0 !important;
    }

    /* Form-Gruppen & Inputs */
    .input-group {
      margin-bottom: 1rem;
    }

    .input-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
    }

    .text-input {
      width: 100%;
      box-sizing: border-box;
      padding: .7rem 1rem;
      border-radius: 10px;
      border: 1px solid #dcdcdc;
      font-size: 1rem;
    }
  </style>
</head>
<body>
  <!-- Globaler Admin-Header (Navigation, Benutzerinformationen) -->
  <?php include ROOT_PATH . '/public/admin/adminHeader.php'; ?>
  <!-- Hauptlayout-Container des Admin-Bereichs -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar mit allen Admin-Menüpunkten -->
    <?php include ROOT_PATH . '/public/admin/adminSidebar.php'; ?>

    <!-- Rechter Arbeitsbereich für Benutzerverwaltung -->
    <div class="admin-content">
      <!-- Aktionen: neuen Benutzer anlegen oder Benutzerliste anzeigen -->
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">Add User</a>
        <a href="index.php"  class="btn btn--lg btn--ghost">Manage Users</a>
      </div>

      <!-- Hauptinhalt: Formular zum Erstellen eines neuen Benutzers -->
      <div class="content">
        <h2 class="page-title">Add User</h2>

        <!-- Validierungsfehler aus der Benutzererstellung anzeigen -->
        <?php if ($errors): ?>
          <div class="msg error"><ul><?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?></ul></div>
        <?php endif; ?>

        <!-- Formular zum Anlegen eines neuen Users -->
        <form action="create.php" method="post" autocomplete="off">
          <!-- CSRF-Schutz für sichere Formularübermittlung -->
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="input-group">
            <!-- Benutzername des neuen Accounts -->
            <label for="username">Username</label>
            <input id="username" name="username" type="text" class="text-input"
                   value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <div class="input-group">
            <!-- E-Mail-Adresse des Benutzers -->
            <label for="email">Email</label>
            <input id="email" name="email" type="email" class="text-input"
                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
          </div>

          <div class="input-group">
            <!-- Passwort für den neuen Benutzer -->
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="text-input" required>
          </div>

          <div class="input-group">
            <!-- Passwort zur Sicherheit erneut eingeben -->
            <label for="passwordConf">Password Confirmation</label>
            <input id="passwordConf" name="passwordConf" type="password" class="text-input" required>
          </div>

          <div class="input-group">
            <!-- Benutzer optional als Administrator markieren -->
            <label class="checkbox">
              <input type="checkbox" name="admin" value="1" <?= $adminFlag ? 'checked' : '' ?>>
              <span>Als Admin markieren</span>
            </label>
          </div>

          <div class="input-group">
            <!-- Benutzer anlegen oder Vorgang abbrechen -->
            <button type="submit" name="create_user" class="btn btn--primary">Create</button>
            <a href="index.php" class="btn btn--ghost">Abbrechen</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Admin-JavaScript & UI-Funktionen laden -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
