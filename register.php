<?php
/* Veränderungsdatum: 08.10.2024 
   Register-Seite (OOP). Legt User via AuthController an.
   Fehler bleiben erhalten, Felder (außer Passwörter) werden vorbefüllt.
*/
require 'path.php';
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\AuthController;
use App\OOP\Repositories\DbRepository;

$auth = new AuthController(new DbRepository());

// Submit?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register-btn'])) {
    $auth->handleRegister($_POST); // redirect oder setzt $_SESSION['form_errors'] / ['form_old']
}

// View-Daten laden + Session aufräumen
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

// Legacy-Variablen, damit vorhandenes Markup unverändert bleiben kann
$username      = $old['username']      ?? '';
$email         = $old['email']         ?? '';
$password      = ''; // niemals vorbefüllen
$passwordConf  = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- CSS Stil -->
  <link rel="stylesheet" href="assets/css/style.css">

  <title>Register</title>
</head>

<body>
  <!-- Header -->
  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>
  <?php include(ROOT_PATH . "/app/includes/messages.php"); ?>

  <div class="auth-content" style="max-width:520px;margin:40px auto;">
    <form action="register.php" method="post">
      <h2 class="form-title">Register</h2>

      <!-- Fehlerliste -->
      <?php include(ROOT_PATH . "/app/helpers/formErrors.php"); ?>

      <!-- Honeypot (unsichtbar für Menschen) -->
      <input type="text" id="honeypot" name="honeypot" value="" style="position:absolute; left:-9999px;" autocomplete="off"/>

      <div>
        <label>Username</label>
        <input type="text" name="username"
               value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
               class="text-input" autocomplete="username">
      </div>

      <div>
        <label>Email</label>
        <input type="email" name="email"
               value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
               class="text-input" autocomplete="email">
      </div>

      <div>
        <label>Password</label>
        <input type="password" name="password" class="text-input" autocomplete="new-password">
      </div>

      <div>
        <label>Password Confirmation</label>
        <input type="password" name="passwordConf" class="text-input" autocomplete="new-password">
      </div>

      <div>
        <button type="submit" name="register-btn" class="btn btn-big">Register</button>
      </div>
      <p>Or <a href="<?php echo BASE_URL . '/login.php'; ?>">Login</a></p>
    </form>
  </div>

  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="assets/js/scripts.js"></script>
</body>
</html>
