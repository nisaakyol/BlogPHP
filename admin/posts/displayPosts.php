<!-- TOP displayPosts.php -->
<?php if (!empty($_SESSION['admin'])): ?>
  <?php foreach ($posts as $key => $post): ?>
    <tr>
      <td><?php echo $key + 1; ?></td>
      <td><?php echo htmlspecialchars($post['title']); ?></td>
      <td><?php echo htmlspecialchars($usersById[(int)$post['user_id']] ?? 'unknown'); ?></td>
      <td><a href="edit.php?id=<?php echo (int)$post['id']; ?>" class="edit">edit</a></td>
      <td><a href="index.php?delete_id=<?php echo (int)$post['id']; ?>" class="delete">delete</a></td>
      <?php if (!empty($post['published'])): ?>
        <td><a href="index.php?published=0&p_id=<?php echo (int)$post['id']; ?>" class="unpublish">unpublish</a></td>
      <?php else: ?>
        <td><a href="index.php?published=1&p_id=<?php echo (int)$post['id']; ?>" class="publish">publish</a></td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <?php $anzahlPost = 1; ?>
  <?php foreach ($posts as $post): ?>
    <?php if ((int)$_SESSION['id'] === (int)$post['user_id']): ?>
      <tr>
        <td><?php echo $anzahlPost++; ?></td>
        <td><?php echo htmlspecialchars($post['title']); ?></td>
        <td><?php echo htmlspecialchars($usersById[(int)$post['user_id']] ?? 'unknown'); ?></td>
        <td><a href="edit.php?id=<?php echo (int)$post['id']; ?>" class="edit">edit</a></td>
        <td><a href="index.php?delete_id=<?php echo (int)$post['id']; ?>" class="delete">delete</a></td>
      </tr>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>
