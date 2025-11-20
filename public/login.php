<?php
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/AuthController.php';

use App\Http\Controllers\AuthController;
use App\Infrastructure\Repositories\DbRepository;

$auth = new AuthController(new DbRepository());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $auth->handleLogin($_POST);
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$username = $old['username'] ?? '';
$siteKey  = getenv('RECAPTCHA_V2_SITE') ?: '';
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">

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

  <?php if ($siteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <?php endif; ?>
</head>

<body>

<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>
<?php include(ROOT_PATH . "/app/Support/includes/messages.php"); ?>

<div class="auth-content">
    <h2>Login</h2>

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
