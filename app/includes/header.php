<!-- Veränderungsdatum: 08.10.2024 
      Header für alle Php-Seiten mit Verlinkungen auf weitere Seiten. 
      Überprüfung der eingeloggten Person mit der DB mittels Session auf Admin, User oder Gast.
-->

<!-- Linke Navigationsleiste -->
<header>

<link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png' ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png' ?>">
    <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest' ?>">


  <a href="<?php echo BASE_URL . '/index.php' ?>" class="logo">
    <h1 class="logo-text"><span>DH</span>BW Stuttgart</h1>
  </a>

  <!-- Login Navigationsbar -->
  <i class="fa fa-bars menu-toggle"></i>
  <ul class="nav">
    <li><a href="<?php echo BASE_URL . '/index.php' ?>">Home</a></li>
    <li><a href="<?php echo BASE_URL . '/hardcode/About.php' ?>">About</a></li>
    <li><a href="https://www.dhbw-stuttgart.de/service/">Services</a></li>

    <!-- Wenn User eingeloggt zeige Username -->
    <?php if (isset($_SESSION['id'])): ?>
      <li>
        <a href="#">
          <i class="fa fa-user"></i>
          <?php echo $_SESSION['username']; ?>
          <i class="fa fa-chevron-down" style="font-size: .8em;"></i>
        </a>
        <ul>

          <!-- Wenn Admin sich einloggt zeige Admin Dashboard. Falls USer sich einloggt zeige User Dashboard. Ansonsten nur Logout -->
          <?php if ($_SESSION['admin']): ?>
            <li><a href="<?php echo BASE_URL . '/admin/dashboard.php' ?>">Dashboard</a></li>
          <?php elseif (isset($_SESSION['id'])): ?>
            <li><a href="<?php echo BASE_URL . '/users/dashboard.php' ?>">Dashboard</a></li>
          <?php endif; ?>

          <li><a href="<?php echo BASE_URL . '/logout.php' ?>" class="logout">Logout</a></li>
        </ul>
      </li>

      <!-- Wenn nicht eingeloggt zeige Sign Up und Login -->
    <?php else: ?>
      <li><a href="<?php echo BASE_URL . '/register.php' ?>">Sign Up</a></li>
      <li><a href="<?php echo BASE_URL . '/login.php' ?>">Login</a></li>

    <?php endif; ?>

  </ul>
</header>