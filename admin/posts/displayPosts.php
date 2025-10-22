<?php
/**
 * Datei: admin/posts/displayPosts.php
 * Zweck: Zeilen-Ausgabe für die Posts-Tabelle
 *
 * Erwartet:
 *   - $posts     (Array der Posts)
 *   - $usersById (Map [user_id => username])
 *
 * Hinweis:
 *   Diese Datei rendert NUR <tr>…</tr>-Zeilen. KEIN <table>, KEIN <thead>!
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once ROOT_PATH . '/app/helpers/csrf.php';

$isAdmin       = !empty($_SESSION['admin']);
$currentUserId = (int)($_SESSION['id'] ?? 0);

$e  = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$sn = 0;

foreach ($posts as $post):
  $postId    = (int)($post['id'] ?? 0);
  $ownerId   = (int)($post['user_id'] ?? 0);
  $ownerName = $usersById[$ownerId] ?? 'unknown';
  $isOwn     = ($ownerId === $currentUserId);
  $status    = (string)($post['status'] ?? 'draft');

  // Normale User: nur eigene Posts anzeigen
  if (!$isAdmin && !$isOwn) continue;

  // Status-Badge
  $badgeMap = [
    'draft'     => ['Draft',     'background:#eee;color:#333;'],
    'submitted' => ['Submitted', 'background:#fff3cd;color:#856404;'],
    'approved'  => ['Approved',  'background:#d4edda;color:#155724;'],
    'rejected'  => ['Rejected',  'background:#f8d7da;color:#721c24;'],
  ];
  $badge = $badgeMap[$status] ?? ['?', 'opacity:.6;'];

  $sn++;
?>
<tr>
  <!-- SN -->
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;"><?= $sn ?></td>

  <!-- Title -->
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;">
    <?= $e($post['title'] ?? '') ?>
  </td>

  <!-- Author -->
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;"><?= $e($ownerName) ?></td>

  <!-- Status -->
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;">
    <span style="padding:.2rem .4rem;border-radius:.3rem;<?= $badge[1] ?>"><?= $badge[0] ?></span>
  </td>

  <!-- Actions (Edit/Delete) -->
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;white-space:nowrap;">
    <a href="edit.php?id=<?= $postId ?>" class="btn btn--sm btn--success">
      <i class="fas fa-pen"></i> Edit
    </a>
    <a href="index.php?delete_id=<?= $postId ?>"
       class="btn btn--sm btn--danger"
       data-confirm="Post wirklich löschen?">
      <i class="fas fa-trash"></i> Delete
    </a>
  </td>

  <!-- Moderation (nur Admin) -->
  <?php if ($isAdmin): ?>
  <td style="padding:.5rem;border-bottom:1px solid #f2f2f2;white-space:nowrap;">
    <?php if ($status !== 'approved'): ?>
      <form action="moderate.php" method="post" style="display:inline-block;margin-right:.25rem;">
        <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <input type="hidden" name="action"  value="approve">
        <input type="text" name="note" placeholder="Note (optional)" style="max-width:160px;">
        <button class="btn btn--sm btn--primary"><i class="fas fa-check"></i> Approve</button>
      </form>
    <?php endif; ?>

    <?php if ($status !== 'rejected'): ?>
      <form action="moderate.php" method="post" style="display:inline-block;">
        <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <input type="hidden" name="action"  value="reject">
        <input type="text" name="note" placeholder="Note (optional)" style="max-width:160px;">
        <button class="btn btn--sm btn--warning"><i class="fas fa-times"></i> Reject</button>
      </form>
    <?php endif; ?>
  </td>
  <?php endif; ?>
</tr>
<?php endforeach; ?>

<?php if ($sn === 0): ?>
<tr>
  <td colspan="<?= $isAdmin ? 6 : 5 ?>" style="padding:.75rem;opacity:.7;">
    Du hast noch keine Posts. <a href="create.php">Jetzt neuen Post erstellen</a>.
  </td>
</tr>
<?php endif; ?>

<script>
  // Bestätigungsdialog für Links mit data-confirm
  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;
    if (!confirm(el.getAttribute('data-confirm') || 'Sicher?')) {
      e.preventDefault();
    }
  }, { passive: false });
</script>
