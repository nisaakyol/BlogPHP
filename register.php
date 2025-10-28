<?php
require 'path.php';
require_once ROOT_PATH . '/app/includes/bootstrap.php';

use App\OOP\Controllers\AuthController;
use App\OOP\Repositories\DbRepository;

$auth = new AuthController(new DbRepository());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register-btn'])) {
    $auth->handleRegister($_POST);
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$username = $old['username'] ?? '';
$email    = $old['email'] ?? '';
$siteKey  = getenv('RECAPTCHA_V2_SITE') ?: '';
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Registrieren</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .btn[disabled] { opacity:.5; cursor:not-allowed; }
    .recaptcha-box{margin:.75rem 0;}
  </style>
  <?php if ($siteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <?php endif; ?>
</head>
<body>
<?php include(ROOT_PATH . "/app/includes/header.php"); ?>
<?php include(ROOT_PATH . "/app/includes/messages.php"); ?>

<div class="auth-content">
  <form action="register.php" method="post" autocomplete="off" id="register-form">
    <!-- Honeypot gegen Bots -->
    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
      <label>Feld frei lassen</label>
      <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
    </div>

    <div>
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="text-input" required>
    </div>
    <div>
      <label>E-Mail</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="text-input" required>
    </div>
    <div>
      <label>Passwort</label>
      <input type="password" name="password" class="text-input" required>
    </div>
    <div>
      <label>Passwort bestätigen</label>
      <input type="password" name="passwordConf" class="text-input" required>
    </div>

    <?php if ($siteKey !== ''): ?>
      <div class="recaptcha-box">
        <!-- reCAPTCHA v2 Checkbox -->
        <div class="g-recaptcha"
             data-sitekey="<?php echo htmlspecialchars($siteKey,ENT_QUOTES,'UTF-8');?>"
             data-callback="onCaptchaOK_register"
             data-expired-callback="onCaptchaExpired_register"
             data-error-callback="onCaptchaExpired_register"></div>
      </div>
    <?php endif; ?>

    <div>
      <button type="submit" name="register-btn" class="btn btn-big" id="register-submit" <?php echo $siteKey!==''?'disabled':''; ?>>
        Registrieren
      </button>
    </div>
  </form>
</div>

<?php if ($siteKey !== ''): ?>
<script>
function onCaptchaOK_register(){ document.getElementById('register-submit').disabled = false; }
function onCaptchaExpired_register(){ document.getElementById('register-submit').disabled = true; }
</script>
<?php endif; ?>
</body>
</html>
