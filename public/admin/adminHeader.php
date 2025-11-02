<header role="banner">

  <a class="logo" href="<?php echo BASE_URL . '/public/index.php'; ?>" aria-label="Startseite">
    <h1 class="logo-text">BLOG</h1>
  </a>

  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <ul class="nav" role="navigation" aria-label="Admin Navigation">
    <?php if (isset($_SESSION['id'])): ?>
      <li class="nav-user">
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user" aria-hidden="true"></i>
          <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); // Anzeigename ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;" aria-hidden="true"></i>
        </a>
        <ul class="dropdown" role="menu">
          <li>
            <a href="<?php echo BASE_URL . '/public/logout.php'; ?>" class="logout" role="menuitem">Logout</a> <!-- Logout-Link -->
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>
</header>
