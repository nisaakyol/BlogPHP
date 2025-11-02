<?php
// Datei: app/Support/includes/header_nav.php
// Zweck: Header mit Hauptnavigation, User-/Admin-Dropdown und Auth-Links
?>

<header role="banner">
  <!-- Menü-Toggle (mobil) -->
  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <!-- Hauptnavigation -->
  <ul class="nav" role="navigation" aria-label="Hauptnavigation">
    <li><a href="<?php echo BASE_URL . '/public/index.php'; ?>">Home</a></li>
    <li><a href="<?php echo BASE_URL . '/public/resources/static/about.php'; ?>">About</a></li>

    <?php if (isset($_SESSION['id'])): ?>
      <!-- Eingeloggt: Username + Dropdown -->
      <li>
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user" aria-hidden="true"></i>
          <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;" aria-hidden="true"></i>
        </a>
        <ul class="dropdown" role="menu">
          <?php if (!empty($_SESSION['admin'])): ?>
            <!-- Admin-Dashboard -->
            <li><a href="<?php echo BASE_URL . '/public/admin/dashboard.php'; ?>" role="menuitem">Dashboard</a></li>
          <?php else: ?>
            <!-- User-Dashboard -->
            <li><a href="<?php echo BASE_URL . '/public/users/dashboard.php'; ?>" role="menuitem">Dashboard</a></li>
          <?php endif; ?>
          <li><a href="<?php echo BASE_URL . '/public/logout.php'; ?>" class="logout" role="menuitem">Logout</a></li>
        </ul>
      </li>
    <?php else: ?>
      <!-- Nicht eingeloggt: Sign Up / Login -->
      <li><a href="<?php echo BASE_URL . '/public/register.php'; ?>">Sign Up</a></li>
      <li><a href="<?php echo BASE_URL . '/public/login.php'; ?>">Login</a></li>
    <?php endif; ?>
  </ul>
</header>
