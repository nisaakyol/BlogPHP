<?php
declare(strict_types=1);

require_once __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/helpers/middleware.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';

use App\OOP\Repositories\DbRepository;

usersOnly();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$userId = (int)($_SESSION['id'] ?? 0);
$repo   = new DbRepository();

// --- Signierten Preview-Link-Builder (7 Tage gültig) --------------------------
$previewLink = function (int $pid): string {
    $secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
    $exp    = time() + 7*24*60*60; // 7 Tage
    $sig    = hash_hmac('sha256', $pid . '|preview|' . $exp, $secret);
    return BASE_URL . "/preview.php?id={$pid}&exp={$exp}&sig={$sig}";
};

// Tab-Ermittlung
$tab = $_GET['tab'] ?? 'posts';
if (!in_array($tab, ['posts', 'account'], true)) $tab = 'posts';

// Passwort-Änderung (nur im Account-Tab verarbeiten)
if ($tab === 'account'
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['current_password'], $_POST['new_password'], $_POST['new_password_confirmation'])) {

    $current = (string)($_POST['current_password'] ?? '');
    $new     = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['new_password_confirmation'] ?? '');
    $errors  = [];

    $user = $repo->selectOne('users', ['id' => $userId]);
    if (!$user) {
        $errors[] = 'Benutzer nicht gefunden.';
    } else {
        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Bitte alle Felder ausfüllen.';
        }
        if (!password_verify($current, (string)($user['password'] ?? ''))) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Neues Passwort und Bestätigung stimmen nicht überein.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'Neues Passwort muss mindestens 6 Zeichen haben.';
        }
        if ($current !== '' && hash_equals($current, $new)) {
            $errors[] = 'Neues Passwort darf nicht dem alten entsprechen.';
        }
    }

    if ($errors) {
        $_SESSION['errors'] = $errors;
    } else {
        $repo->update('users', $userId, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        $_SESSION['message'] = 'Passwort erfolgreich geändert.';
        $_SESSION['type']    = 'success';
    }
    header('Location: ' . BASE_URL . '/users/dashboard.php?tab=account');
    exit;
}

// Eigene Posts laden (für Posts-Tab)
$myPosts = $repo->selectAll('posts', ['user_id' => $userId], 'created_at DESC');

// Helper
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

function statusBadge(?string $status): string {
    $status = $status ?: 'draft';
    $map = [
        'draft'     => ['Draft',     'background:#eee;color:#333;'],
        'submitted' => ['Submitted', 'background:#fff3cd;color:#856404;'],
        'approved'  => ['Approved',  'background:#d4edda;color:#155724;'],
        'rejected'  => ['Rejected',  'background:#f8d7da;color:#721c24;'],
    ];
    [$label, $style] = $map[$status] ?? ['?', 'opacity:.6;'];
    return '<span style="padding:.2rem .5rem;border-radius:.3rem;'.$style.'">'.$label.'</span>';
}

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <title>Dein Dashboard</title>
  <style>
    .card { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.07); padding:18px; }
    .table { width:100%; border-collapse:collapse; }
    .table th, .table td { padding:.6rem; border-bottom:1px solid #f2f2f2; vertical-align:top; }
  </style>
</head>
<body>
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="content">
        <h2 class="page-title">Dein Dashboard</h2>
        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <?php if ($tab === 'posts'): ?>
          <div class="button-group" style="margin-bottom:1rem;">
            <a href="<?= BASE_URL ?>/admin/posts/create.php" class="btn btn-big">Neuen Post erstellen</a>
          </div>

          <div class="card">
            <h3>Deine Posts</h3>
            <table class="table">
                <thead>
                    <tr>
                    <th class="col-sn">#</th>
                    <th>Title</th>
                    <th class="col-status">Status</th>
                    <th class="col-note">Review-Notiz</th>
                    <th class="col-actions">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($myPosts as $i => $p): ?>
                    <?php $pid = (int)$p['id']; $status = (string)($p['status'] ?? 'draft'); ?>
                    <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= $e($p['title'] ?? '') ?></td>
                    <td><?= statusBadge($status) ?></td>
                    <td class="t-cell--note">
                        <?php $note = trim((string)($p['review_note'] ?? ''));
                        echo $note !== '' ? nl2br($e($note)) : '<span style="opacity:.6;">—</span>'; ?>
                    </td>
                    <td class="col-actions">
                        <div class="actions">
                        <a href="<?= $previewLink($pid) ?>" class="btn btn--sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="<?= BASE_URL ?>/admin/posts/edit.php?id=<?= $pid ?>" class="btn btn--sm btn--success">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <a href="<?= BASE_URL ?>/users/delete-post.php?id=<?= $pid ?>" class="btn btn--sm btn--danger" data-confirm="Wirklich löschen?">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        <?php if (in_array($status, ['draft','rejected'], true)): ?>
                            <a href="<?= BASE_URL ?>/users/submit.php?id=<?= $pid ?>" class="btn btn--sm"
                            onclick="return confirm('Beitrag zur Prüfung einreichen?');">
                            <i class="fas fa-paper-plane"></i> Einreichen
                            </a>
                        <?php endif; ?>
                        </div>
                    </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
          </div>

        <?php else: /* tab === 'account' */ ?>
          <div class="card" style="max-width:560px; margin:0 auto;">
            <h3>Passwort ändern</h3>

            <?php if (!empty($errors)): ?>
              <div class="msg error">
                <ul>
                  <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/users/dashboard.php?tab=account" autocomplete="off">
              <div class="input-group">
                <label for="current_password">Aktuelles Passwort</label>
                <input id="current_password" name="current_password" type="password" class="text-input" required autocomplete="current-password">
              </div>

              <div class="input-group">
                <label for="new_password">Neues Passwort</label>
                <input id="new_password" name="new_password" type="password" class="text-input" required autocomplete="new-password">
              </div>

              <div class="input-group">
                <label for="new_password_confirmation">Neues Passwort (Bestätigung)</label>
                <input id="new_password_confirmation" name="new_password_confirmation" type="password" class="text-input" required autocomplete="new-password">
              </div>

              <div class="input-group">
                <button type="submit" class="btn btn--primary">Passwort speichern</button>
              </div>
            </form>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script>
    // Bestätigungsdialoge für Delete/Submit
    document.addEventListener('click', function (e) {
      const el = e.target.closest('[data-confirm]');
      if (!el) return;
      if (!confirm(el.getAttribute('data-confirm') || 'Sicher?')) {
        e.preventDefault();
      }
    }, { passive: false });
  </script>
</body>
</html>
