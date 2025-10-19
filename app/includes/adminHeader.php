<!-- Veränderungsdatum: 08.10.2024 
      Dashboard Header mit eingeloggten User
-->

<header>

<link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png' ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png' ?>">
    <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest' ?>">

    <a class="logo" href="<?php echo BASE_URL . '/index.php'; ?>">
        <h1 class="logo-text"><span>DH</span>BW Stuttgart</h1>
    </a>
    <i class="fa fa-bars menu-toggle"></i>
    <ul class="nav">

        <?php if (isset($_SESSION['id'])): ?>
            <li>
                <a href="#">
                    <i class="fa fa-user"></i>
                    <?php echo $_SESSION['username']; ?>
                    <i class="fa fa-chevron-down" style="font-size: .8em;"></i>
                </a>
                <ul>
                    <li><a href="<?php echo BASE_URL . '/logout.php'; ?>" class="logout">Logout</a></li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</header>