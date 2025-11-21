<?php
// Zweck: Rendert die <tr>-Zeilen für die Posts-Tabelle inkl. Aktionen & Moderation

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';

$isAdmin       = !empty($_SESSION['admin'] ?? 0);
$currentUserId = (int)($_SESSION['id'] ?? 0);

$e  = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$sn = 0;

$badgeMap = [
  'draft'     => ['Draft',     'background:#eee;color:#333;'],
  'submitted' => ['Submitted', 'background:#fff3cd;color:#856404;'],
  'approved'  => ['Approved',  'background:#d4edda;color:#155724;'],
  'rejected'  => ['Rejected',  'background:#f8d7da;color:#721c24;'],
];

foreach ($posts as $post):
  $postId    = (int)($post['id'] ?? 0);
  $ownerId   = (int)($post['user_id'] ?? 0);
  $ownerName = (string)($usersById[$ownerId] ?? 'unknown');
  $isOwn     = ($ownerId === $currentUserId);
  $status    = (string)($post['status'] ?? 'draft');

  // normale User sehen nur eigene Posts
  if (!$isAdmin && !$isOwn) continue;

  $badge = $badgeMap[$status] ?? ['?', 'opacity:.6;'];

  // Submit nur für Besitzer (nicht Admin) bei draft/rejected
  $canSubmit = (!$isAdmin) && $isOwn && in_array($status, ['draft','rejected'], true);

  // signierter Preview-Link (7 Tage gültig)
  $secret  = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
  $exp     = time() + 7*24*60*60;
  $sig     = hash_hmac('sha256', $postId . '|preview|' . $exp, $secret);
  $viewUrl = BASE_URL . "/public/preview.php?id={$postId}&exp={$exp}&sig={$sig}";

  $sn++;
?>
<tr>
  <td><?= $sn ?></td>

  <td class="t-cell--title"><?= $e($post['title'] ?? '') ?></td>

  <td><?= $e($ownerName) ?></td>

  <td>
    <span class="badge" style="<?= $badge[1] ?>"><?= $badge[0] ?></span>
  </td>

    <td class="col-actions">
    <div class="actions">

      <!-- VIEW -->
      <a href="<?= $viewUrl ?>" class="btn-chip btn-chip--view">
        <i class="fas fa-eye"></i> View
      </a>

      <!-- EDIT + DELETE nur für eigene Posts -->
      <?php if ($isOwn): ?>
        <a href="<?= BASE_URL ?>/public/admin/posts/edit.php?id=<?= $postId ?>" class="btn-chip btn-chip--edit">
          <i class="fas fa-pen"></i> Edit
        </a>

        <!-- Delete via POST + CSRF -->
        <form action="<?= BASE_URL ?>/public/admin/posts/postActions.php"
              method="post"
              style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="post_id" value="<?= $postId ?>">
          <button type="submit"
                  class="btn-chip btn-chip--delete"
                  data-confirm="Post wirklich löschen?">
            <i class="fas fa-trash"></i> Delete
          </button>
        </form>
      <?php endif; ?>

      <!-- Benutzer kann den Beitrag zur Prüfung einreichen (nur normale User, kein Admin) -->
      <?php if ($canSubmit): ?>
        <form action="<?= BASE_URL ?>/public/admin/posts/postActions.php"
              method="post"
              style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
          <input type="hidden" name="action" value="submit">
          <input type="hidden" name="post_id" value="<?= $postId ?>">
          <button type="submit"
                  class="btn btn--sm"
                  data-confirm="Beitrag zur Prüfung einreichen?">
            <i class="fas fa-paper-plane"></i> Submit
          </button>
        </form>
      <?php endif; ?>

    </div>
  </td>
  <!-- Admin-Bereich: Fremde Beiträge moderieren (Approve/Reject) -->
  <?php if ($isAdmin && !$isOwn): ?>
    <td class="col-note">
      <div class="actions">
        <!-- Admin-Option: Beitrag freigeben, wenn er noch nicht genehmigt wurde -->
        <?php if ($status !== 'approved'): ?>
          <form action="<?= BASE_URL ?>/public/admin/posts/moderate.php" method="post" style="display:inline-flex; gap:.35rem; align-items:center;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="action"  value="approve">
            <input type="hidden" name="note"    value="" class="js-note-input">
            <button type="button" class="btn btn--sm btn--success js-open-note" data-title="<?= $e($post['title'] ?? '') ?>">
              <i class="fas fa-check"></i> Approve
            </button>
          </form>
        <?php endif; ?>

        <!-- Admin-Option: Beitrag ablehnen, solange er noch nicht abgelehnt ist -->
        <?php if ($status !== 'rejected'): ?>
          <form action="<?= BASE_URL ?>/public/admin/posts/moderate.php" method="post" style="display:inline-flex; gap:.35rem; align-items:center;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="action"  value="reject">
            <input type="hidden" name="note"    value="" class="js-note-input">
            <button type="button" class="btn btn--sm btn--warning js-open-note" data-title="<?= $e($post['title'] ?? '') ?>">
              <i class="fas fa-times"></i> Reject
            </button>
          </form>
        <?php endif; ?>
      </div>
    </td>
  <?php endif; ?>
</tr>
<?php endforeach; ?>

<?php if ($sn === 0): ?>
<tr>
  <td colspan="<?= $isAdmin ? 6 : 5 ?>" style="padding:.75rem;opacity:.7;">
    Keine Posts vorhanden. <a href="<?= BASE_URL ?>/public/admin/posts/create.php">Jetzt neuen Post erstellen</a>.
  </td>
</tr>
<?php endif; ?>

<script>
  // Bestätigung für Buttons/Links mit data-confirm
  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;
    if (!confirm(el.getAttribute('data-confirm') || 'Sicher?')) {
      e.preventDefault();
    }
  }, { passive: false });

  // Notiz-Dialog für Approve/Reject (füllt hidden "note" und submitted das Formular)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-open-note');
    if (!btn) return;

    e.preventDefault();

    const form = btn.closest('form');
    const noteField = form ? form.querySelector('.js-note-input') : null;
    if (!form || !noteField) return;

    const title  = btn.getAttribute('data-title') || '';
    const action = form.querySelector('input[name="action"]')?.value || '';

    const prefix = action === 'approve' ? 'Freigeben' : 'Ablehnen';
    const note   = window.prompt(prefix + (title ? ' – ' + title : '') + '\nOptional: kurze Notiz für den Autor:', '');

    if (note === null) return;

    noteField.value = (note || '').trim();
    form.submit();
  });
</script>
