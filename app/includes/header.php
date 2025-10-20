<?php
/**
 * Datei: app/includes/header.php
 * Zweck: Gemeinsamer Seiten-Header mit Navigation (öffentlich)
 *
 * Hinweise:
 * - Favicons/Manifest liegen hier im Partial; idealer Ort wäre <head>, bleibt aber kompatibel.
 * - Username wird ge-escaped ausgegeben.
 * - Admin-Erkennung via !empty($_SESSION['admin']).
 */
?>
<!-- Linke Navigationsleiste -->
<header role="banner">
  <!-- Favicons / Manifest -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png'; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png'; ?>">
  <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest'; ?>">

  <!-- Logo / Startseite -->
  <a href="<?php echo BASE_URL . '/index.php'; ?>" class="logo" aria-label="Startseite">
    <h1 class="logo-text"><span>DH</span>BW Stuttgart</h1>
  </a>

  <!-- Menü-Toggle (mobil) -->
  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <!-- Hauptnavigation -->
  <ul class="nav" role="navigation" aria-label="Hauptnavigation">
    <li><a href="<?php echo BASE_URL . '/index.php'; ?>">Home</a></li>
    <li><a href="<?php echo BASE_URL . '/hardcode/About.php'; ?>">About</a></li>
    <li><a href="https://www.dhbw-stuttgart.de/service/">Services</a></li>

    <!-- Wenn User eingeloggt: Username + Dropdown -->
    <?php if (isset($_SESSION['id'])): ?>
      <li>
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user" aria-hidden="true"></i>
          <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;" aria-hidden="true"></i>
        </a>
        <ul class="dropdown" role="menu">
          <!-- Admin sieht Admin-Dashboard, sonst User-Dashboard -->
          <?php if (!empty($_SESSION['admin'])): ?>
            <li><a href="<?php echo BASE_URL . '/admin/dashboard.php'; ?>" role="menuitem">Dashboard</a></li>
          <?php else: ?>
            <li><a href="<?php echo BASE_URL . '/users/dashboard.php'; ?>" role="menuitem">Dashboard</a></li>
          <?php endif; ?>

          <li><a href="<?php echo BASE_URL . '/logout.php'; ?>" class="logout" role="menuitem">Logout</a></li>
        </ul>
      </li>

    <!-- Wenn nicht eingeloggt: Sign Up / Login -->
    <?php else: ?>
      <li><a href="<?php echo BASE_URL . '/register.php'; ?>">Sign Up</a></li>
      <li><a href="<?php echo BASE_URL . '/login.php'; ?>">Login</a></li>
    <?php endif; ?>
  </ul>
</header>
