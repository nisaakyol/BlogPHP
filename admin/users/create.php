<?php
/**
 * Datei: admin/users/create.php
 * Zweck: Admin-Formular zum Anlegen eines neuen Users
 *
 * Hinweise:
 * - Zugriff nur für Admins (adminOnly()).
 * - Controller (app/controllers/users.php) setzt Formularwerte/Fehler.
 * - Passwortfelder werden aktuell wiederbefüllt (UX), sicherer wäre: keine Vorbefüllung.
 */

require __DIR__ . '/../_admin_boot.php';
usersOnly();


require_once ROOT_PATH . "/app/controllers/users.php";

// Defensiv: Defaults, falls der Controller Variablen nicht setzt
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

  <title>Admin Section - Add User</title>
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
        <h2 class="page-title">Add User</h2>

        <!-- Validierungsfehler -->
        <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

        <!--
          Formular zum Anlegen eines Users
          - action: create.php (dieselbe Seite; Controller verarbeitet POST)
          - Hinweis: Für Produktion CSRF-Token ergänzen
        -->
        <form action="create.php" method="post">
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

          <!-- Passwort (Hinweis: Vorbefüllung ist sicherheitstechnisch nicht empfohlen) -->
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
              <input type="checkbox" name="admin" <?php echo ($admin === 1) ? 'checked' : ''; ?>>
