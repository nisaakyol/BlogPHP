<?php
// app/includes/adminSidebar.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin = !empty($_SESSION['admin']);
?>
<div class="left-sidebar">
  <ul>

    <?php if ($isAdmin): ?>

      <li>
        <a href="<?php echo BASE_URL; ?>/admin/posts/index.php">Manage Posts</a>
      </li>

      <!-- Falls du auch Topics/Users verwaltest, hier lassen -->
      <li><a href="<?php echo BASE_URL; ?>/admin/topics/index.php">Manage Topics</a></li>
      <li><a href="<?php echo BASE_URL; ?>/admin/users/index.php">Manage Users</a></li>

    <?php else: ?>
      <!-- Normale User -->
      <ul>
        <li>
          <a href="<?= BASE_URL ?>/users/dashboard.php?tab=posts"
            class="<?= (($_GET['tab'] ?? 'posts') === 'posts') ? 'active' : '' ?>">
            Manage Posts
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>/users/dashboard.php?tab=account"
            class="<?= (($_GET['tab'] ?? 'posts') === 'account') ? 'active' : '' ?>">
            Manage Account
          </a>
        </li>
      </ul>
    <?php endif; ?>

  </ul>
</div>
<?php



