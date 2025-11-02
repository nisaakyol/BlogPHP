<?php
declare(strict_types=1);

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
// Hinweis: Datei heißt "DBRepository.php", Klasse ist "DbRepository" – Case kann auf Linux relevant sein.
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';

use App\Infrastructure\Repositories\DbRepository;

// Ausgabe als HTML (UTF-8)
header('Content-Type: text/html; charset=utf-8');

// // Eingangsparameter aus der URL lesen
// $id  : Post-ID
// $exp : Ablaufzeitpunkt (Unix-Timestamp) des Vorschau-Links
// $sig : HMAC-Signatur zur Verifikation
$id  = (int)($_GET['id']  ?? 0);
$exp = (int)($_GET['exp'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

// // Signatur prüfen
// Secret aus Konstante/ENV (Fallback 'dev'); HMAC bildet die Server-seitige Prüfsumme
$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
$calc   = hash_hmac('sha256', $id . '|preview|' . $exp, $secret);

// // Ungültige oder abgelaufene Links sofort blocken
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

// // Datensatz laden
$repo = new DbRepository();
$post = $repo->selectOne('posts', ['id' => $id]);

// // 404, wenn Post nicht existiert
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

// // Kurzhelper für HTML-Escaping
$e      = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
// // Titel/Body/Status vorbereiten
$title  = $e($post['title'] ?? '(ohne Titel)');
// Body bleibt absichtlich unescaped, da es formatierte Inhalte enthalten kann
$body   = (string)($post['body'] ?? '');
$status = (string)($post['status'] ?? 'draft');

// // Autor-Daten lesen (optional)
$authorId   = (int)($post['user_id'] ?? 0);
$authorRow  = $authorId ? $repo->selectOne('users', ['id' => $authorId]) : null;
$authorName = $e($authorRow['username'] ?? $authorRow['name'] ?? 'Unbekannt');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vorschau: <?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/assets/css/style.css">
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
            <div class="meta">Autor: <?= $authorName ?></div>
            <article class="content">
                <?= $body ?>
            </article>
        </main>
    </div>
</body>
</html>
