<?php
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/AuthController.php';

use App\Http\Controllers\AuthController;          // Login-Controller
use App\Infrastructure\Repositories\DbRepository; // DB-Repo (DI)

$auth = new AuthController(new DbRepository());   // Controller mit Repo

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $auth->handleLogin($_POST);                   // Login verarbeiten
}

$errors = $_SESSION['form_errors'] ?? [];         // Validierungsfehler
$old    = $_SESSION['form_old'] ?? [];            // Alte Eingaben
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$username = $old['username'] ?? '';
$siteKey  = getenv('RECAPTCHA_V2_SITE') ?: '';    // reCAPTCHA v2 Site-Key (optional)
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <style>
    .btn[disabled] { opacity:.5; cursor:not-allowed; }
    .recaptcha-box{margin:.75rem 0;}
  </style>
  <?php if ($siteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- reCAPTCHA v2 -->
  <?php endif; ?>
</head>
<body>
<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>
<?php include(ROOT_PATH . "/app/Support/includes/messages.php"); ?>

<div class="auth-content">
  <form action="login.php" method="post" autocomplete="off" id="login-form">
    <div>
      <label>Username oder E-Mail</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="text-input" required>
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" class="text-input" required>
    </div>

    <?php if ($siteKey !== ''): ?>
      <div class="recaptcha-box">
        <div class="g-recaptcha"
             data-sitekey="<?php echo htmlspecialchars($siteKey,ENT_QUOTES,'UTF-8');?>"
             data-callback="onCaptchaOK_login"
             data-expired-callback="onCaptchaExpired_login"
             data-error-callback="onCaptchaExpired_login"></div> <!-- Captcha-Status steuert Button -->
      </div>
    <?php endif; ?>

    <div>
      <button type="submit" name="login-btn" class="btn btn-big" id="login-submit" <?php echo $siteKey!==''?'disabled':''; ?>>
        Login
      </button> <!-- bei Captcha initial disabled -->
    </div>
  </form>
</div>

<?php if ($siteKey !== ''): ?>
<script>
function onCaptchaOK_login(){ document.getElementById('login-submit').disabled = false; }   // Captcha ok → aktivieren
function onCaptchaExpired_login(){ document.getElementById('login-submit').disabled = true; } // abgelaufen/Fehler → sperren
</script>
<?php endif; ?>
</body>
</html>
