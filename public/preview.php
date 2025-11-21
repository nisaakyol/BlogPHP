<?php
declare(strict_types=1);

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';

use App\Infrastructure\Repositories\DbRepository;

header('Content-Type: text/html; charset=utf-8');

// Parameter
$id  = (int)($_GET['id']  ?? 0);
$exp = (int)($_GET['exp'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

// Signatur prüfen
$secret = defined('EMAIL_LINK_SECRET') ? EMAIL_LINK_SECRET : 'dev';
$calc   = hash_hmac('sha256', $id . '|preview|' . $exp, $secret);

if ($id <= 0 || $exp <= time() || !hash_equals($calc, $sig)) {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="de">
    <meta charset="utf-8">
    <title>Vorschau – ungültig</title>
    <style>
        body{
            font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;
            background:#efe7dd;
            padding:2rem;
        }
    </style>
    <h2>Vorschau-Link ungültig oder abgelaufen.</h2>
    <p><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Zur Startseite</a></p>
    </html>
    <?php
    exit;
}

// Post laden
$repo  = new DbRepository();
$post  = $repo->selectOne('posts', ['id' => $id]);

if (!$post) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="de">
    <meta charset="utf-8">
    <title>Vorschau – nicht gefunden</title>
    <style>
        body{
            font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;
            background:#efe7dd;
            padding:2rem;
        }
    </style>
    <h2>Beitrag nicht gefunden.</h2>
    <p><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Zur Startseite</a></p>
    </html>
    <?php
    exit;
}

// Helper
$e      = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$title  = $e($post['title'] ?? '(ohne Titel)');
$body   = (string)($post['body'] ?? '');
$status = (string)($post['status'] ?? 'draft');

// Autor
$authorId   = (int)($post['user_id'] ?? 0);
$authorRow  = $authorId ? $repo->selectOne('users', ['id' => $authorId]) : null;
$authorName = $e($authorRow['username'] ?? $authorRow['name'] ?? 'Unbekannt');

// Bilddaten
$imageName = (string)($post['image'] ?? '');
$imgUrl    = $imageName !== ''
    ? BASE_URL . '/public/resources/assets/images/' . rawurlencode($imageName)
    : null;

$imgAlt = trim((string)($post['image_alt'] ?? ''));
if ($imgAlt === '') {
    $imgAlt = $post['title'] ?? 'Beitragsbild';
}
$imgCap = trim((string)($post['image_caption'] ?? ''));
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vorschau: <?= $title ?></title>

    <style>
        /* Hintergrund Sand */
        body{
            margin:0;
            padding:30px 0;
            background:#efe7dd;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, sans-serif;
        }

        /* Weiße Karte */
        .wrap{
            max-width:900px;
            margin:0 auto;
            background:#ffffff;
            border-radius:22px;
            box-shadow:0 18px 42px rgba(0,0,0,.08);
            overflow:hidden;
        }

        /* Header */
        header{
            padding:16px 22px;
            background:#2e3a46; /* dein Blau */
            color:#ffffff;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        header strong{
            font-size:1.1rem;
            letter-spacing:0.4px;
        }

        .badge{
            background:rgba(255,255,255,0.2);
            padding:.35rem .8rem;
            border-radius:999px;
            text-transform:uppercase;
            font-size:.8rem;
        }

        main{
            padding:24px 32px 36px;
        }

        h1{
            margin:.2em 0 .6em;
            font-size:2rem;
            font-weight:700;
            color:#1f2937;
        }

        .meta{
            color:#6b7280;
            font-size:.95rem;
            margin-bottom:1.2rem;
        }

        /* Hero-Bild wie im Blog */
        .hero{
            margin:0 0 1.5rem;
        }

        .hero img{
            display:block;
            width:100%;
            max-height:340px;
            object-fit:cover;
            border-radius:16px;
        }

        .hero figcaption{
            font-size:.9rem;
            color:#9ca3af;
            margin-top:.4rem;
        }

        .content{
            line-height:1.65;
            font-size:1.05rem;
            color:#1f1f1f;
        }

        .content img{
            max-width:100%;
            height:auto;
            border-radius:10px;
            margin:10px 0;
        }
    </style>
</head>
<body>

<!-- Post-Vorschau-Layout für Titel, Bild und Inhalt -->
<div class="wrap">
    <!-- Kopfbereich der Vorschau: Titel & Status-Badge -->
    <header>
        <strong>Vorschau</strong>
        <span class="badge"><?= $e(strtoupper($status)) ?></span>
    </header>

    <!-- Hauptinhalt der Vorschau: Titel, Meta, Bild und Artikel -->
    <main>
        <h1><?= $title ?></h1>
        <div class="meta">Autor: <?= $authorName ?></div>

        <!-- Beitragsbild mit optionaler Bildunterschrift -->
        <?php if ($imgUrl): ?>
            <figure class="hero">
                <img src="<?= $e($imgUrl) ?>" alt="<?= $e($imgAlt) ?>">
                <?php if ($imgCap !== ''): ?>
                    <figcaption><?= $e($imgCap) ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <!-- Gerenderter Beitragstext (HTML) -->
        <article class="content">
            <?= $body ?>
        </article>
    </main>
</div>

</body>
</html>
