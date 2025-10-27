<?php
/**
 * Datei: app/includes/adminHeader.php
 * Zweck: Header/Navigationsleiste für den Adminbereich (eingeloggter User)
 *
 * Hinweise:
 * - Favicons/Manifest liegen hier im Header-Partial; ideal wäre <head>, bleibt aber kompatibel.
 * - Username-Ausgabe ist ge-escaped.
 * - Navigation erhält ARIA-Attribute für bessere Zugänglichkeit.
 */
?>
<header role="banner">
  <!-- Favicons / Manifest -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png'; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png'; ?>">
  <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest'; ?>">

  <!-- Logo / Startseite -->
  <a class="logo" href="<?php echo BASE_URL . '/index.php'; ?>" aria-label="Startseite">
    <h1 class="logo-text">BLOG</h1>
  </a>

  <!-- Mobile Menü-Icon -->
  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <!-- Hauptnavigation -->
  <ul class="nav" role="navigation" aria-label="Admin Navigation">
    <?php if (isset($_SESSION['id'])): ?>
      <li class="nav-user">
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user" aria-hidden="true"></i>
          <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;" aria-hidden="true"></i>
        </a>
        <ul class="dropdown" role="menu">
          <li>
            <a href="<?php echo BASE_URL . '/logout.php'; ?>" class="logout" role="menuitem">Logout</a>
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>
</header>
