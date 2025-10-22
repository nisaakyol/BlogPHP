<?php
/**
 * Datei: admin/users/edit.php
 * Zweck: Admin-Formular zum Aktualisieren eines bestehenden Users
 *
 * Hinweise:
 * - Zugriff nur für Admins (adminOnly()).
 * - Controller (app/controllers/users.php) liefert Formularwerte/Fehler.
 * - Sicherheit: username/email werden ge-escaped; id wird als int ausgegeben.
 * - UX/Sicherheit: Passwortfelder sind vorbefüllt (wie Vorlage) – produktiv besser leer lassen.
 */

require __DIR__ . '/../_admin_boot.php';
usersOnly();


require_once ROOT_PATH . "/app/controllers/users.php";

// Defensiv-Defaults, falls der Controller Variablen nicht gesetzt hat
$id            = isset($id) ? (int)$id : 0;
$username      = $username      ?? '';
$email         = $email         ?? '';
$password      = $password      ?? '';
$passwordConf  = $passwordConf  ?? '';
$admin         = isset($admin) ? (int)$admin : 0;
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

  <title>Admin Section - Edit User</title>
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
        <h2 class="page-title">Edit User</h2>

        <!-- Validierungsfehler -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Aktualisieren eines Users
          - action: edit.php (dieselbe Seite; Controller verarbeitet POST)
          - Hinweis: Für Produktion CSRF-Token ergänzen
        -->
        <form action="edit.php" method="post">
          <!-- User-ID (hidden) -->
          <input type="hidden" name="id" value="<?php echo $id; ?>">

          <!-- Username -->
          <div>
            <label for="username">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              class="text-input"
              value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>

          <!-- E-Mail -->
          <div>
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              class="text-input"
              value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>

          <!-- Passwort (Hinweis: in Produktion nicht vorbefüllen) -->
          <div>
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              class="text-input"
              value="<?php echo $password; ?>"
            >
          </div>

          <!-- Passwort-Bestätigung -->
          <div>
            <label for="passwordConf">Password Confirmation</label>
            <input
              type="password"
              id="passwordConf"
              name="passwordConf"
              class="text-input"
              value="<?php echo $passwordConf; ?>"
            >
          </div>

          <!-- Admin-Flag -->
          <div>
            <label>
              <input
                type="checkbox"
                name="admin"
                <?php echo ($admin === 1) ? 'checked' : ''; ?>
              >
              Admin
            </label>
          </div>

          <!-- Absenden -->
          <div>
            <button type="submit" name="update-user" class="btn btn-big">Update User</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor (hier nicht zwingend nötig) -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
