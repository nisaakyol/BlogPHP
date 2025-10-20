<?php
/* Register-Seite (OOP). Legt User via AuthController an.
   Fehler bleiben erhalten, Felder (außer Passwörter) werden vorbefüllt.
*/
require 'path.php';                                     // Projektpfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/OOP/bootstrap.php';      // Bootstrap/Autoloader der OOP-Schicht

use App\OOP\Controllers\AuthController;                 // Zuständig für Register-/Login-Flow
use App\OOP\Repositories\DbRepository;                  // DB-Zugriffsschicht (per DI)

// Controller mit Repository injizieren
$auth = new AuthController(new DbRepository());

// Submit?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register-btn'])) {
    // Übergibt Formdaten an den Controller:
    // - Bei Erfolg: Redirect
    // - Bei Fehler: setzt $_SESSION['form_errors'] und $_SESSION['form_old']
    $auth->handleRegister($_POST);
}

// View-Daten laden + Session aufräumen (Flash-Pattern)
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']); // nach Konsum entfernen

// Sticky Values (niemals Passwörter vorbefüllen!)
$username     = $old['username'] ?? '';
$email        = $old['email']    ?? '';
$password     = ''; // nie vorbefüllen
$passwordConf = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">                                   <!-- Basis-Encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">     <!-- IE-Kompatibilität -->

  <!-- Font Awesome (Icons) -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- CSS Stil -->
  <link rel="stylesheet" href="assets/css/style.css">

  <title>Register</title>
</head>
<body>
  <!-- Header + globale Hinweise/Messages -->
  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>
  <?php include(ROOT_PATH . "/app/includes/messages.php"); ?>

  <div class="auth-content" style="max-width:520px;margin:40px auto;">
    <!-- Formular postet an dieselbe Seite; Controller verarbeitet oben -->
    <form action="register.php" method="post" autocomplete="off">
      <h2 class="form-title">Register</h2>

      <!-- Fehlerliste (nutzt $errors aus oben) -->
      <?php include(ROOT_PATH . "/app/helpers/formErrors.php"); ?>

      <!-- Honeypot (Bot-Schutz; für Nutzer unsichtbar) -->
      <input type="text" id="honeypot" name="honeypot" value="" style="position:absolute; left:-9999px;" autocomplete="off"/>

      <!-- Username -->
      <div>
        <label for="username">Username</label>
        <input type="text"
               id="username"
               name="username"
               class="text-input"
               autocomplete="username"
               value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
      </div>

      <!-- Email -->
      <div>
        <label for="email">Email</label>
        <input type="email"
               id="email"
               name="email"
               class="text-input"
               autocomplete="email"
               value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
      </div>

      <!-- Password -->
      <div>
        <label for="password">Password</label>
        <input type="password"
               id="password"
               name="password"
               class="text-input"
               autocomplete="new-password">
      </div>

      <!-- Password Confirmation -->
      <div>
        <label for="passwordConf">Password Confirmation</label>
        <input type="password"
               id="passwordConf"
               name="passwordConf"
               class="text-input"
               autocomplete="new-password">
      </div>

      <div>
        <button type="submit" name="register-btn" class="btn btn-big">Register</button>
      </div>

      <p>Or <a href="<?php echo BASE_URL . '/login.php'; ?>">Login</a></p>
    </form>
  </div>

  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?> <!-- Globale Fußzeile -->

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> <!-- jQuery -->
  <script src="assets/js/scripts.js"></script> <!-- Projekt-Skripte -->
</body>
</html>
