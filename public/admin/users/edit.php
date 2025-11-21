<?php
require __DIR__ . '/../_admin_boot.php';
adminOnly();

require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminUserController.php';

use App\Http\Controllers\Admin\AdminUserController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminUserController(new DbRepository()); // Controller mit Repo

$id = (int)($_GET['id'] ?? 0); // Benutzer-ID aus Query
if ($id <= 0) {
    $_SESSION['message'] = 'Ungültige Benutzer-ID.';
    $_SESSION['type']    = 'error';
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->update($id, $_POST); // Update verarbeiten
    exit;
}

// GET → Daten laden
$vm     = $ctrl->edit($id);
$user   = $vm['user']   ?? ['username'=>'','email'=>'','admin'=>0];
$errors = $_SESSION['errors'] ?? $vm['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

// Helper: old > user
$val = function(string $key, $default = '') use ($old, $user) {
    if (array_key_exists($key, $old))   return (string)$old[$key];
    if (array_key_exists($key, $user))  return (string)$user[$key];
    return (string)$default;
};
$adminChecked = (int)($old['admin'] ?? $user['admin'] ?? 0) === 1; // Checkbox-Status
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section - Edit User</title>
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

    /* Button-Gruppe oben */
/* Button-Gruppe oben */
.button-group {
  display: flex;
  flex-direction: column;   /* untereinander */
  gap: 10px;                /* Abstand */
   margin: 20px 0 18px;     /* <<< HIER KORRIGIERT */
      align-items: flex-start;
}

/* globale Regel aus admin.css überschreiben:
   zweiter Button NICHT mehr eingerückt */
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
  <!-- Globaler Admin-Header mit Navigation und Benutzerinformationen -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <!-- Hauptlayout-Container des Adminbereichs -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar mit den Admin-Menüpunkten -->
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <!-- Rechter Arbeitsbereich für die Benutzerbearbeitung -->
    <div class="admin-content">
      <!-- Aktionen: Neuen Benutzer anlegen oder Benutzerübersicht öffnen -->
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">Add User</a>
        <a href="index.php"  class="btn btn--lg btn--ghost">Manage Users</a>
      </div>

      <!-- Formularbereich zum Bearbeiten eines bestehenden Benutzers -->
      <div class="content">
        <h2 class="page-title">Edit User</h2>

        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

        <!-- Validierungsfehler für das Bearbeitungsformular -->
        <?php if (!empty($errors)): ?>
          <div class="msg error">
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Formular zum Aktualisieren der Benutzerdaten -->
        <form method="post" action="edit.php?id=<?= (int)$id ?>">
          <div class="input-group">
            <!-- Benutzername des Accounts -->
            <label for="username">Username *</label>
            <input id="username" name="username" type="text" class="text-input" required
                   value="<?= htmlspecialchars($val('username'), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="input-group">
            <!-- E-Mail-Adresse des Benutzers -->
            <label for="email">Email *</label>
            <input id="email" name="email" type="email" class="text-input" required
                   value="<?= htmlspecialchars($val('email'), ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="input-group">
            <!-- Optional neues Passwort setzen -->
            <label for="password">Neues Passwort (optional)</label>
            <input id="password" name="password" type="password" class="text-input" autocomplete="new-password">
          </div>

          <div class="input-group">
          <!-- Neues Passwort wiederholen (nur falls geändert) -->
            <label for="passwordConf">Passwort wiederholen</label>
            <input id="passwordConf" name="passwordConf" type="password" class="text-input" autocomplete="new-password">
          </div>

          <div class="input-group">
            <!-- Benutzer als Administrator markieren -->
            <label class="checkbox">
              <input type="checkbox" name="admin" value="1" <?= $adminChecked ? 'checked' : '' ?>>
              Admin
            </label>
          </div>

          <div class="input-group">
            <!-- Änderungen speichern oder Vorgang abbrechen -->
            <button type="submit" href="index.php" class="btn btn--primary">Update User</button>
            <a href="index.php" class="btn btn--ghost">Abbrechen</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Admin-JavaScript für Formular- und UI-Funktionen -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
