<?php
/**
 * Datei: admin/posts/displayPosts.php
 * Zweck: Komplette tabellarische Ausgabe der Posts im Adminbereich
 *
 * Erwartet (vom Controller bereitgestellt):
 *   - $posts      : array<int, array{id:int,title:string,user_id:int,published:int|bool,...}>
 *   - $usersById  : array<int, string>  (user_id => username)
 *
 * Abhängigkeiten:
 *   - \App\OOP\Services\AuthService
 *   - \App\OOP\Services\AccessService
 *   - \App\OOP\Repositories\PostRepository
 *   - \App\OOP\Repositories\CommentRepository
 *
 * Verhalten:
 *   - Admins sehen alle Posts inkl. Publish/Unpublish.
 *   - Normale User sehen nur Buttons für eigene Posts (Edit/Delete).
 *   - Alle gefährlichen Aktionen laufen weiterhin über index.php per GET (Legacy).
 *     → Für Produktion bitte auf POST + CSRF umstellen.
 */

use App\OOP\Services\AuthService;
use App\OOP\Services\AccessService;
use App\OOP\Repositories\PostRepository;
use App\OOP\Repositories\CommentRepository;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usersById      = $usersById ?? [];
$auth           = new AuthService();
$access         = new AccessService($auth, new PostRepository(), new CommentRepository());
$isAdmin        = $access->isAdmin();
$currentUserId  = (int)($access->currentUserId() ?? 0);

// Hilfsfunktion für sicheres Escaping
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!-- displayPosts.php: BEGIN -->
<table class="table" style="width:100%; border-collapse:collapse">
  <thead>
    <tr>
      <th style="text-align:left;padding:.5rem;border-bottom:1px solid #eee;">#</th>
      <th style="text-align:left;padding:.5rem;border-bottom:1px solid #eee;">Title</th>
      <th style="text-align:left;padding:.5rem;border-bottom:1px solid #eee;">Author</th>
      <th style="text-align:left;padding:.5rem;border-bottom:1px solid #eee;">Actions</th>
      <?php if ($isAdmin): ?>
        <th style="text-align:left;padding:.5rem;border-bottom:1px solid #eee;">Publish</th>
      <?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php
      $row = 0;
      foreach ($posts as $idx => $post):
        $postId      = (int)($post['id'] ?? 0);
        $ownerId     = (int)($post['user_id'] ?? 0);
        $ownerName   = $usersById[$ownerId] ?? 'unknown';
        $canManage   = $access->canManagePost($postId);
        $isPublished = !empty($post['published']);
        // Für Admins numerieren wir alle; für User nur die eigenen
        $rowNumber   = $isAdmin ? ($idx + 1) : ($ownerId === $currentUserId ? (++$row) : null);

        // Normale User: Zeile nur rendern, wenn eigener Post ODER (zur Anzeige) der Controller bereits filtert.
        if (!$isAdmin && $ownerId !== $currentUserId) {
            continue;
        }
    ?>
      <tr>
        <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;">
          <?= $e($rowNumber ?? ($idx + 1)); ?>
        </td>

        <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;">
          <?= $e($post['title'] ?? ''); ?>
          <?php if ($isPublished): ?>
            <span title="Published" style="display:inline-block;margin-left:.35rem;font-size:.75rem;opacity:.7;">●</span>
          <?php else: ?>
            <span title="Unpublished" style="display:inline-block;margin-left(.35rem);font-size:.75rem;opacity:.4;">○</span>
          <?php endif; ?>
        </td>

        <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;">
          <?= $e($ownerName); ?>
        </td>

        <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;white-space:nowrap;">
          <?php if ($canManage): ?>
            <a href="edit.php?id=<?= $postId; ?>" class="btn btn--sm btn--success">
              <i class="fas fa-pen"></i> Edit
            </a>
            <a href="index.php?delete_id=<?= $postId; ?>"
               class="btn btn--sm btn--danger"
               data-confirm="Post wirklich löschen?">
              <i class="fas fa-trash"></i> Delete
            </a>
          <?php else: ?>
            <span style="opacity:.6;">Keine Aktionen</span>
          <?php endif; ?>
        </td>

        <?php if ($isAdmin): ?>
          <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;white-space:nowrap;">
            <?php if ($isPublished): ?>
              <a href="index.php?published=0&amp;p_id=<?= $postId; ?>"
                 class="btn btn--sm btn--warning"
                 data-confirm="Diesen Post wirklich UNPUBLISHEN (offline schalten)?">
                <i class="fas fa-eye-slash"></i> Unpublish
              </a>
            <?php else: ?>
              <a href="index.php?published=1&amp;p_id=<?= $postId; ?>"
                 class="btn btn--sm btn--primary">
                <i class="fas fa-eye"></i> Publish
              </a>
            <?php endif; ?>
          </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>

    <?php if (!$isAdmin && $row === 0): ?>
      <tr>
        <td colspan="<?= $isAdmin ? 5 : 4; ?>" style="padding:.75rem;opacity:.7;">
          Du hast noch keine Posts. <a href="create.php">Jetzt neuen Post erstellen</a>.
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<script>
  // Bestätigungsdialog für data-confirm
  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;
    const msg = el.getAttribute('data-confirm') || 'Sicher?';
    if (!confirm(msg)) {
      e.preventDefault();
    }
  }, { passive: false });
</script>
<!-- displayPosts.php: END -->
