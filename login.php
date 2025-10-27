<?php
require 'path.php'; // Lädt Projektpfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap.php'; // Bootstrap/Autoloader der OOP-Schicht

use App\OOP\Controllers\AuthController; // Controller für Authentifizierungsvorgänge
use App\OOP\Repositories\DbRepository;  // DB-Repository (CRUD, Queries)

$auth = new AuthController(new DbRepository()); // Controller mit Repository injizieren

// POST-Handling: Login-Formular abgesendet?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $auth->handleLogin($_POST); // Validierung + Authentifizierung + Session/Redirects
}

// Flash-Formdaten/-Fehler aus Session für Re-Render (Sticky-Form)
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']); // nach einmaligem Zugriff leeren

$username = $old['username'] ?? ''; // Vorbelegung des Username-Felds
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style.css"> <!-- Basis-Styles -->
</head>
<body>
<?php include(ROOT_PATH . "/app/includes/header.php"); ?>   <!-- Globale Navigation -->
<?php include(ROOT_PATH . "/app/includes/messages.php"); ?> <!-- System-/Fehlermeldungen -->

<div class="auth-content">
  <!-- Login-Formular: POST nach login.php; AutoFill deaktiviert -->
  <form action="login.php" method="post" autocomplete="off">
    <div>
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="text-input">
      <!-- Hinweis: Fehlerausgabe könnte hier ergänzt werden (z. B. $errors['username']) -->
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" class="text-input">
      <!-- Sensible Eingabe: keine Vorbelegung -->
    </div>
    <div>
      <button type="submit" name="login-btn" class="btn btn-big">Login</button>
    </div>
  </form>
</div>
</body>
</html>
