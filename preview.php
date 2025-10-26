<?php
declare(strict_types=1);

/**
 * Datei: preview.php
 * Zweck: Geschützte Vorschau für unveröffentlichte Beiträge über signierte Links.
 * Signatur: sig = HMAC-SHA256("{$id}|preview|{$exp}", EMAIL_LINK_SECRET)
 */

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';

use App\OOP\Repositories\DbRepository;

header('Content-Type: text/html; charset=utf-8');

// --- Eingaben ----------------------------------------------------------------
$id  = (int)($_GET['id']  ?? 0);
$exp = (int)($_GET['exp'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
$calc   = hash_hmac('sha256', $id . '|preview|' . $exp, $secret);

// --- Validierung --------------------------------------------------------------
if ($id <= 0 || $exp <= time() || !hash_equals($calc, $sig)) {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="de"><meta charset="utf-8">
    <title>Vorschau – ungültig</title>
    <style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;padding:2rem;}</style>
    <h2>Vorschau-Link ungültig oder abgelaufen.</h2>
    <p><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Zur Startseite</a></p>
    </html>
    <?php
    exit;
}

// --- Daten holen (auch wenn status != 'approved') ----------------------------
$repo = new DbRepository();
$post = $repo->selectOne('posts', ['id' => $id]);

if (!$post) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="de"><meta charset="utf-8">
    <title>Vorschau – nicht gefunden</title>
    <style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;padding:2rem;}</style>
    <h2>Beitrag nicht gefunden.</h2>
    <p><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Zur Startseite</a></p>
    </html>
    <?php
    exit;
}

// --- Ausgabe (read-only Vorschau) --------------------------------------------
$e        = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$title    = $e($post['title'] ?? '(ohne Titel)');
$body     = (string)($post['body'] ?? ''); // enthält evtl. HTML vom Editor
$status   = (string)($post['status'] ?? 'draft');

// Autor-Name statt User-ID anzeigen
$authorId   = (int)($post['user_id'] ?? 0);
$authorRow  = $authorId ? $repo->selectOne('users', ['id' => $authorId]) : null;
// Fallback: 'username' bevorzugt, sonst 'name', sonst 'Unbekannt'
$authorName = $e($authorRow['username'] ?? $authorRow['name'] ?? 'Unbekannt');
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vorschau: <?= $title ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif; background:#f6f7fb; margin:0;}
    .wrap{max-width:880px; margin:40px auto; background:#fff; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,.08); overflow:hidden;}
    header{padding:14px 18px; background:#0ea5e9; color:#fff; display:flex; align-items:center; gap:.6rem;}
    header .badge{background:rgba(255,255,255,.2); padding:.25rem .6rem; border-radius:.5rem;}
    main{padding:26px;}
    h1{margin:.2em 0 .6em; font-size:1.9rem;}
    .meta{color:#6b7280; font-size:.95rem; margin-bottom:1rem;}
    .content{line-height:1.65;}
    .content img{max-width:100%; height:auto;}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <strong>Vorschau</strong>
      <span class="badge"><?= $e(strtoupper($status)) ?></span>
    </header>
    <main>
      <h1><?= $title ?></h1>
      <div class="meta"> Autor: <?= $authorName ?></div>
      <article class="content">
        <?= $body /* Preview zeigt WYSIWYG-HTML */ ?>
      </article>
    </main>
  </div>
</body>
</html>
