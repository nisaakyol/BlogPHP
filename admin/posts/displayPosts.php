<?php
/**
 * Datei: admin/posts/displayPosts.php
 * Zweck: Zeilen-Ausgabe für die Posts-Tabelle
 *
 * Erwartet Variablen im Scope:
 *   - $posts     (Array der Posts)
 *   - $usersById (Map [user_id => username])
 *   - $isAdmin   (bool) – wird in index.php gesetzt
 *
 * Hinweis:
 *   Diese Datei rendert NUR <tr>…</tr>-Zeilen. KEIN <table>, KEIN <thead>!
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once ROOT_PATH . '/app/helpers/csrf.php';

$isAdmin       = !empty($_SESSION['admin']);
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

  // Normale User: nur eigene Posts sehen
  if (!$isAdmin && !$isOwn) continue;

  $badge = $badgeMap[$status] ?? ['?', 'opacity:.6;'];

  // Submit nur für Besitzer UND keine Admins (Admin soll keinen Submit sehen)
  $canSubmit = (!$isAdmin) && $isOwn && in_array($status, ['draft','rejected'], true);

  // Signierten Preview-Link bauen (7 Tage gültig)
  $secret  = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
  $exp     = time() + 7*24*60*60;
  $sig     = hash_hmac('sha256', $postId . '|preview|' . $exp, $secret);
  $viewUrl = BASE_URL . "/preview.php?id={$postId}&exp={$exp}&sig={$sig}";

  $sn++;
?>
<tr>
  <!-- SN -->
  <td><?= $sn ?></td>

  <!-- Title -->
  <td class="t-cell--title"><?= $e($post['title'] ?? '') ?></td>

  <!-- Author -->
  <td><?= $e($ownerName) ?></td>

  <!-- Status -->
  <td>
    <span class="badge" style="<?= $badge[1] ?>"><?= $badge[0] ?></span>
  </td>

  <!-- Actions (View/Edit/Delete [+ Submit für Besitzer, nicht Admin]) -->
  <td class="col-actions">
    <div class="actions">
      <a href="<?= $viewUrl ?>" class="btn btn--sm">
        <i class="fas fa-eye"></i> View
      </a>

      <?php if ($isOwn): ?>
        <a href="edit.php?id=<?= $postId ?>" class="btn btn--sm btn--success">
          <i class="fas fa-pen"></i> Edit
        </a>
        <a href="index.php?delete_id=<?= $postId ?>"
           class="btn btn--sm btn--danger"
           data-confirm="Post wirklich löschen?">
          <i class="fas fa-trash"></i> Delete
        </a>
      <?php endif; ?>

      <?php if ($canSubmit): ?>
        <!-- Dieser Link triggert in admin/posts/index.php den submit-Handler,
             der PostWriteController::submit($id) aufruft und die Mail verschickt -->
        <a href="index.php?submit_id=<?= $postId ?>"
           class="btn btn--sm"
           onclick="return confirm('Beitrag zur Prüfung einreichen?');">
          <i class="fas fa-paper-plane"></i> Submit
        </a>
      <?php endif; ?>
    </div>
  </td>

  <!-- Moderation (nur Admin) – bleibt POST wie gehabt, Note via prompt() -->
  <?php if ($isAdmin): ?>
    <td class="col-note">
      <div class="actions">
        <?php if ($status !== 'approved'): ?>
          <form action="moderate.php" method="post" style="display:inline-flex; gap:.35rem; align-items:center;">
            <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="action"  value="approve">
            <input type="hidden" name="note"    value="" class="js-note-input">
            <button type="button" class="btn btn--sm btn--success js-open-note" data-title="<?= $e($post['title'] ?? '') ?>">
              <i class="fas fa-check"></i> Approve
            </button>
          </form>
        <?php endif; ?>

        <?php if ($status !== 'rejected'): ?>
          <form action="moderate.php" method="post" style="display:inline-flex; gap:.35rem; align-items:center;">
            <input type="hidden" name="csrf" value="<?= csrf_token(); ?>">
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
    Keine Posts vorhanden. <a href="create.php">Jetzt neuen Post erstellen</a>.
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

  // Notiz-Popup für Approve/Reject (Moderation bleibt POST an moderate.php)
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-open-note');
    if (!btn) return;

    e.preventDefault();

    // passendes Formular + hidden note-Feld finden
    const form = btn.closest('form');
    const noteField = form ? form.querySelector('.js-note-input') : null;
    if (!form || !noteField) return;

    const title = btn.getAttribute('data-title') || '';
    const action = form.querySelector('input[name="action"]')?.value || '';

    // einfache Prompt-Variante (kein zusätzliches Modal-Markup nötig)
    const prefix = action === 'approve' ? 'Freigeben' : 'Ablehnen';
    const note = window.prompt(prefix + (title ? ' – ' + title : '') + '\nOptional: kurze Notiz für den Autor:', '');

    if (note === null) {
      // Abbrechen
      return;
    }

    noteField.value = note.trim();
    form.submit();
  });
</script>
