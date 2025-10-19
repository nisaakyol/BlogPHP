<?php
require 'path.php';
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\AuthController;
use App\OOP\Repositories\DbRepository;

$auth = new AuthController(new DbRepository());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $auth->handleLogin($_POST);
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$username = $old['username'] ?? '';
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include(ROOT_PATH . "/app/includes/header.php"); ?>
<?php include(ROOT_PATH . "/app/includes/messages.php"); ?>

<div class="auth-content">
  <form action="login.php" method="post" autocomplete="off">
    <div>
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="text-input">
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" class="text-input">
    </div>
    <div>
      <button type="submit" name="login-btn" class="btn btn-big">Login</button>
    </div>
  </form>
</div>
</body>
</html>
