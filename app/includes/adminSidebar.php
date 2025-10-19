<!-- Veränderungsdatum: 08.10.2024 
      Dashboard Sidebar mit Unterscheidung zwischen Admin (Manage POst, Manage User, Manage Topic) und User (Manage Post)
-->

<div class="left-sidebar">
    <ul>
        <?php if ($_SESSION['admin']): ?>
            <li><a href="<?php echo BASE_URL . '/admin/posts/index.php'; ?>">Manage Posts</a></li>
            <li><a href="<?php echo BASE_URL . '/admin/users/index.php'; ?>">Manage Users</a></li>
            <li><a href="<?php echo BASE_URL . '/admin/topics/index.php'; ?>">Manage Topics</a></li>
        <?php elseif (isset($_SESSION['id'])): ?>
            <li><a href="<?php echo BASE_URL . '/admin/posts/index.php'; ?>">Manage Posts</a></li>
        <?php endif; ?>
    </ul>
</div>