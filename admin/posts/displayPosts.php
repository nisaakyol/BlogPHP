<?php
/**
 * Datei: admin/posts/displayPosts.php
 * Zweck: Tabellarische Ausgabe der Posts im Adminbereich
 *
 * Hinweise:
 * - Admins sehen alle Posts inkl. Aktionen (edit/delete/publish/unpublish).
 * - Normale User sehen nur ihre eigenen Posts (edit/delete).
 * - Aktionen (delete/publish/unpublish) laufen per GET-Parameter über index.php.
 *   → Für Produktion besser per POST + CSRF-Token absichern.
 */

// Robustere Kurzschreibweisen
$isAdmin       = !empty($_SESSION['admin']);
$currentUserId = (int)($_SESSION['id'] ?? 0);

// Erwartete Datenstrukturen:
// $posts:      Liste von Arrays mit Keys: id, title, user_id, published
// $usersById:  Map [user_id => username]
?>

<!-- TOP displayPosts.php -->
<?php if ($isAdmin): ?>
  <?php foreach ($posts as $idx => $post): ?>
    <tr>
      <!-- Laufnummer (1-basiert) -->
      <td><?php echo $idx + 1; ?></td>

      <!-- Titel -->
      <td><?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

      <!-- Autor -->
      <td>
        <?php
          $uid      = (int)($post['user_id'] ?? 0);
          $username = $usersById[$uid] ?? 'unknown';
          echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        ?>
      </td>

      <!-- Aktionen: Edit/Delete -->
      <td>
        <a href="edit.php?id=<?php echo (int)($post['id'] ?? 0); ?>" class="edit">edit</a>
      </td>
      <td>
        <a href="index.php?delete_id=<?php echo (int)($post['id'] ?? 0); ?>" class="delete">delete</a>
      </td>

      <!-- Publish/Unpublish (nur Admin) -->
      <?php $isPublished = !empty($post['published']); ?>
      <?php if ($isPublished): ?>
        <td>
          <a
            href="index.php?published=0&amp;p_id=<?php echo (int)($post['id'] ?? 0); ?>"
            class="unpublish"
          >unpublish</a>
        </td>
      <?php else: ?>
        <td>
          <a
            href="index.php?published=1&amp;p_id=<?php echo (int)($post['id'] ?? 0); ?>"
            class="publish"
          >publish</a>
        </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>

<?php else: ?>
  <?php
    // Laufnummer nur für eigene Posts des eingeloggten Users
    $rowNumber = 1;
  ?>
  <?php foreach ($posts as $post): ?>
    <?php if ($currentUserId === (int)($post['user_id'] ?? -1)): ?>
      <tr>
        <!-- Laufnummer (nur eigene Posts zählen) -->
        <td><?php echo $rowNumber++; ?></td>

        <!-- Titel -->
        <td><?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

        <!-- Autor (hier immer der eingeloggte User) -->
        <td>
          <?php
            $uid      = (int)($post['user_id'] ?? 0);
            $username = $usersById[$uid] ?? 'unknown';
            echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
          ?>
        </td>

        <!-- Aktionen: Edit/Delete (kein Publish für normale User) -->
        <td>
          <a href="edit.php?id=<?php echo (int)($post['id'] ?? 0); ?>" class="edit">edit</a>
        </td>
        <td>
          <a href="index.php?delete_id=<?php echo (int)($post['id'] ?? 0); ?>" class="delete">delete</a>
        </td>
      </tr>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>
