<?php
// Grund-Setup: Pfade, Bootstrap, Repository und Auth-Controller laden
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/AuthController.php';

use App\Http\Controllers\AuthController;
use App\Infrastructure\Repositories\DbRepository;

$auth = new AuthController(new DbRepository());

// Login-Request verarbeiten: Eingaben prüfen und Benutzer authentifizieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $auth->handleLogin($_POST);
}

// Validierungsfehler und alte Formulardaten aus der Session laden und danach entfernen
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$username = $old['username'] ?? '';
$siteKey  = getenv('RECAPTCHA_V2_SITE') ?: '';
?>
<!-- Kopfbereich mit Layout, Login-Styling und optionalem reCAPTCHA -->
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">

  <!-- Login-spezifisches Layout: Hintergrund, Card-Box, Buttons -->
  <style>
      /* === GLOBALER SAND-HINTERGRUND === */
      html, body {
          background: #efe7dd !important;
          margin: 0;
          padding: 0;
      }

      /* Card-Box wie Register */
      .auth-content {
          width: 450px;
          margin: 60px auto;
          padding: 35px 40px;
          background: #ffffff;
          border-radius: 18px;
          box-shadow: 0 18px 45px rgba(0,0,0,.10);
      }

      .auth-content h2 {
          text-align: center;
          margin-bottom: 20px;
      }

      .btn[disabled] { opacity:.5; cursor:not-allowed; }
      .recaptcha-box { margin:.75rem 0; }
  </style>

<!-- reCAPTCHA v2 einbinden, falls konfiguriert -->
  <?php if ($siteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <?php endif; ?>
</head>

<body>

<!-- Öffentlicher Seiten-Header und globale Systemmeldungen -->
<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>
<?php include(ROOT_PATH . "/app/Support/includes/messages.php"); ?>

<!-- Login-Card: Formularcontainer für Benutzeranmeldung -->
<div class="auth-content">
    <h2>Login</h2>

    <!-- Formular zur Benutzeranmeldung -->
    <form action="login.php" method="post" autocomplete="off" id="login-form">

      <div>
        <label>Username oder E-Mail</label>
        <input type="text"
               name="username"
               value="<?= htmlspecialchars($username); ?>"
               class="text-input"
               required>
      </div>

      <div>
        <label>Passwort</label>
        <input type="password" name="password" class="text-input" required>
      </div>

      <!-- reCAPTCHA-Sicherheitsprüfung im Formular -->
      <?php if ($siteKey !== ''): ?>
        <div class="recaptcha-box">
          <div class="g-recaptcha"
               data-sitekey="<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8'); ?>"
               data-callback="onCaptchaOK_login"
               data-expired-callback="onCaptchaExpired_login"
               data-error-callback="onCaptchaExpired_login"></div>
        </div>
      <?php endif; ?>

      <div style="margin-top:15px;">
        <!-- Login absenden (ggf. erst nach erfolgreichem reCAPTCHA aktiv) -->
        <button type="submit"
                name="login-btn"
                class="btn btn-big"
                id="login-submit"
                <?= $siteKey !== '' ? 'disabled' : ''; ?>>
          Login
        </button>
      </div>

    </form>
</div>

<!-- reCAPTCHA-Callback-Funktionen zum Aktivieren/Deaktivieren des Login-Buttons -->
<?php if ($siteKey !== ''): ?>
<script>
function onCaptchaOK_login(){
    document.getElementById('login-submit').disabled = false;
}
function onCaptchaExpired_login(){
    document.getElementById('login-submit').disabled = true;
}
</script>
<?php endif; ?>

</body>
</html>
