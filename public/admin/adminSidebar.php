<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(); // Session sicherstellen
}

$isAdmin = !empty($_SESSION['admin']); // Admin-Flag
?>
<div class="left-sidebar">
  <ul>

    <?php if ($isAdmin): ?>
      <li>
        <a href="<?php echo BASE_URL; ?>/public/admin/posts/index.php">Manage Posts</a>
      </li>
      <li><a href="<?php echo BASE_URL; ?>/public/admin/topics/index.php">Manage Topics</a></li> <!-- optional -->
      <li><a href="<?php echo BASE_URL; ?>/public/admin/users/index.php">Manage Users</a></li>   <!-- optional -->

    <?php else: ?>
      <!-- User-Menü -->
      <ul>
        <li>
          <a href="<?= BASE_URL ?>/public/users/dashboard.php?tab=posts"
            class="<?= (($_GET['tab'] ?? 'posts') === 'posts') ? 'active' : '' ?>">
            Manage Posts
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>/public/users/dashboard.php?tab=account"
            class="<?= (($_GET['tab'] ?? 'posts') === 'account') ? 'active' : '' ?>">
            Manage Account
          </a>
        </li>
      </ul>
    <?php endif; ?>
  </ul>
</div>
<?php
