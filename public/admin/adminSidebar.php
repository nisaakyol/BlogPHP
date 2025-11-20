<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin = !empty($_SESSION['admin']);           // Admin-Flag
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

/**
 * Kleiner Helper für "active"-Klasse anhand der URL
 */
$active = function (string $needle) use ($currentPath): string {
    return (strpos($currentPath, $needle) !== false) ? ' sidebar-link-active' : '';
};
?>

<style>
  /* -----------------------------------------------
     Linke Sidebar – Modern & Sand-kompatibel
  ----------------------------------------------- */
  .left-sidebar {
    width: 240px;
    background: #d4d5d6;              /* dezentes Grau */
    min-height: calc(100vh - 66px);   /* unterhalb Header */
    box-shadow: 6px 0 18px rgba(0,0,0,.08);
    padding-top: 10px;
    box-sizing: border-box;
  }

  .sidebar-nav {
    list-style: none;
    margin: 0;
    padding: 10px 0 20px;
  }

  .sidebar-section-title {
    padding: 10px 18px 4px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #555;
  }

  .sidebar-nav-item {
    margin: 2px 0;
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 18px;
    font-size: 0.95rem;
    color: #222;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
  }

  .sidebar-link-icon {
    font-size: 0.95rem;
    width: 18px;
    text-align: center;
    opacity: .9;
  }

  .sidebar-link:hover {
    background: rgba(255,255,255,.55);
    border-color: #2e3a46;          /* Blau wie Header */
    color: #111;
  }

  .sidebar-link-active {
    background: rgba(255,255,255,.85);
    border-color: #2e3a46;
    font-weight: 600;
  }

  @media (max-width: 900px) {
    .left-sidebar {
      display: none; /* optional: Sidebar auf kleinen Screens ausblenden */
    }
  }
</style>

<div class="left-sidebar">
  <ul class="sidebar-nav">
    <?php if ($isAdmin): ?>
      <li class="sidebar-section-title">Admin</li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL; ?>/public/admin/dashboard.php"
           class="sidebar-link<?= $active('/admin/dashboard.php'); ?>">
          <span class="sidebar-link-icon"><i class="fa fa-tachometer-alt"></i></span>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL; ?>/public/admin/posts/index.php"
           class="sidebar-link<?= $active('/admin/posts'); ?>">
          <span class="sidebar-link-icon"><i class="fa fa-file-alt"></i></span>
          <span>Manage Posts</span>
        </a>
      </li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL; ?>/public/admin/topics/index.php"
           class="sidebar-link<?= $active('/admin/topics'); ?>">
          <span class="sidebar-link-icon"><i class="fa fa-tags"></i></span>
          <span>Manage Topics</span>
        </a>
      </li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL; ?>/public/admin/users/index.php"
           class="sidebar-link<?= $active('/admin/users'); ?>">
          <span class="sidebar-link-icon"><i class="fa fa-users"></i></span>
          <span>Manage Users</span>
        </a>
      </li>

    <?php else: ?>
      <li class="sidebar-section-title">User</li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL ?>/public/users/dashboard.php?tab=posts"
           class="sidebar-link<?= (($_GET['tab'] ?? 'posts') === 'posts') ? ' sidebar-link-active' : ''; ?>">
          <span class="sidebar-link-icon"><i class="fa fa-file-alt"></i></span>
          <span>Manage Posts</span>
        </a>
      </li>

      <li class="sidebar-nav-item">
        <a href="<?= BASE_URL ?>/public/users/dashboard.php?tab=account"
           class="sidebar-link<?= (($_GET['tab'] ?? 'posts') === 'account') ? ' sidebar-link-active' : ''; ?>">
          <span class="sidebar-link-icon"><i class="fa fa-user-cog"></i></span>
          <span>Manage Account</span>
        </a>
      </li>
    <?php endif; ?>
  </ul>
</div>
