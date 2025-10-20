<?php
/**
 * Datei: app/includes/adminSidebar.php
 * Zweck: Linke Sidebar im Dashboard
 *
 * Verhalten:
 * - Admins: Manage Posts, Manage Users, Manage Topics
 * - Normale User: nur Manage Posts
 */
?>
<nav class="left-sidebar" role="navigation" aria-label="Dashboard Navigation">
  <ul>
    <?php if (!empty($_SESSION['admin'])): ?>
      <li>
        <a href="<?php echo BASE_URL . '/admin/posts/index.php'; ?>">
          Manage Posts
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL . '/admin/users/index.php'; ?>">
          Manage Users
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL . '/admin/topics/index.php'; ?>">
          Manage Topics
        </a>
      </li>

    <?php elseif (isset($_SESSION['id'])): ?>
      <li>
        <a href="<?php echo BASE_URL . '/admin/posts/index.php'; ?>">
          Manage Posts
        </a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
