<?php
declare(strict_types=1);
// Zweck: Rendert die Single-Post-Seite inkl. Kommentar-Form, Topics- und Popular-Sidebar.

require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/SingleController.php';
require_once ROOT_PATH . '/app/Http/Controllers/CommentController.php';

use App\Infrastructure\Repositories\DbRepository;
use App\Http\Controllers\SingleController;
use App\Http\Controllers\CommentController;

// POST → Kommentar speichern (Controller macht Redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    (new CommentController(new DbRepository()))->store($_POST);
    exit;
}

// Post-ID aus Query (nur für Action/Links benötigt; das Laden übernimmt der SingleController)
$id = (int)($_GET['id'] ?? 0);

// ViewModel über SingleController aufbauen (liefert $post, $topics, $popular)
$sc = new SingleController();
$sc->boot();
$post    = $sc->post;
$topics  = $sc->topics;
$popular = $sc->popular;

// Helper laden (Kommentare rendern, CSRF, Cookies)
require_once ROOT_PATH . '/app/Support/helpers/comments.php';
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';
require_once ROOT_PATH . '/app/Support/helpers/cookies.php';

// kleine Helper

// Prüft, ob ein Nutzer eingeloggt ist (per Session)
$isLoggedIn = function (): bool {
    return !empty($_SESSION['id']);
};

// Aktueller Username aus der Session (Fallback "user")
$currentUsername = $_SESSION['username'] ?? 'user';

// Cookie-Prefill (Gast)
$prefillName = '';
if (!$isLoggedIn()) {
    $cookie = get_cookie('comment_author');
    if ($cookie) {
        $d = json_decode($cookie, true);
        if (is_array($d)) {
            $prefillName = htmlspecialchars((string)($d['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        }
    }
}

// reCAPTCHA v3 Site-Key (aus ENV)
$recaptchaSiteKey = getenv('RECAPTCHA_V3_SITE') ?: getenv('RECAPTCHA_SITE') ?: '';

// Bild-URL (Hero)
$heroImgUrl = !empty($post['image'])
    ? BASE_URL . '/public/resources/assets/images/' . rawurlencode((string)$post['image'])
    : null;

// HTML escaper (kurzer Alias)
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Debug: CSRF-Token beim Rendern loggen
error_log('CSRF_RENDER sid=' . session_id() . ' token=' . ($_SESSION['csrf']['token'] ?? 'NULL'));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $e($post['title'] ?? 'Beitrag'); ?> | TRAVEL-BLOG</title>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">

    <style>
        /* Single-Ansicht spezifisch: */
        .main-content.single article.post { height: auto !important; overflow: visible !important; background: transparent; box-shadow: none; }
        .main-content.single .post-hero { margin: 0 0 16px; overflow: hidden; line-height: 0 }
        .main-content.single .post-hero img { display: block; width: 100%; height: 140px; object-fit: cover }
        .main-content.single .post-textwrap { overflow: visible !important; max-width: 100%; }
        .main-content.single .post-textwrap * { max-width: 100%; word-break: break-word; overflow-wrap: anywhere; }
        .sidebar.single .section .section-title { font-weight: 700; }
        .sidebar.single .popular .post.clearfix { margin-bottom: .75rem; }
        .sidebar.single .popular img { width: 64px; height: 48px; object-fit: cover; margin-right: .5rem; float:left; }
        .sidebar.single .popular h4 { margin: 0; font-size: .95rem; line-height: 1.2; }
        .sidebar.single .popular small { color: #777; display:block; }
    </style>
</head>
<body>

<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

<div class="page-wrapper">
    <div class="content clearfix">

        <div class="main-content-wrapper main-content single">

            <section class="post-section">
                <article class="post" id="single-post">
                    <header class="post-header">
                        <h1 class="post-title"><?= $e($post['title'] ?? ''); ?></h1>
                    </header>

                    <?php if ($heroImgUrl): ?>
                        <figure class="post-hero">
                            <img src="<?= $heroImgUrl; ?>" alt="<?= $e($post['title'] ?? 'Post image'); ?>">
                        </figure>
                    <?php endif; ?>

                    <div class="post-textwrap">
                        <div class="post-content">
                            <?= html_entity_decode((string)($post['body'] ?? '')); ?>
                        </div>
                    </div>
                </article>
            </section>

            <section class="comment-section" id="comments">
                <?php
                // Flash-Messages (z. B. nach Kommentar-Submit)
                if (!empty($_SESSION['message'])) {
                    $type = $_SESSION['type'] ?? 'success';
                    echo '<div class="flash ' . $type . '">' . $e($_SESSION['message']) . '</div>';
                    unset($_SESSION['message'], $_SESSION['type']);
                }

                // Kommentare anzeigen (Thread/Antworten inkl. Reply-Links)
                if (!empty($post['id'])) {
                    display_comments((int)$post['id']);
                }
                ?>

                <h3 class="comment-title">Kommentar hinzufügen</h3>

                <!-- Kommentarformular (mit CSRF, Honeypot, reCAPTCHA v3) -->
                <form
                    id="comment-form"
                    action="<?= BASE_URL ?>/public/single.php?id=<?= (int)($post['id'] ?? $id); ?>"
                    method="post"
                    class="comment-form"
                    novalidate
                >
                    <!-- CSRF-Token -->
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Eltern-Kommentar-ID (für Antworten/Threads) -->
                    <input type="hidden" name="parent_id" id="parent_id" value="">

                    <!-- Ziel-Post-ID -->
                    <input type="hidden" name="post_id" id="post_id" value="<?= (int)($post['id'] ?? 0); ?>">

                    <!-- reCAPTCHA Felder -->
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                    <input type="hidden" name="recaptcha_action" value="submit_comment">

                    <!-- Honeypot-Feld (Spam-Schutz) -->
                    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
                        <label>Dein Name (frei lassen)</label>
                        <input type="text" name="hp_name" autocomplete="off" tabindex="-1">
                    </div>

                    <?php if ($isLoggedIn()): ?>
                        <p class="muted">
                            Eingeloggt als <strong><?= $e($currentUsername); ?></strong>
                        </p>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="author_name">Name*</label><br>
                            <input id="author_name" name="author_name" type="text" value="<?= $prefillName ?>" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="remember_author" value="1" <?= ($prefillName ? 'checked' : ''); ?>>
                                Name merken
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="comment">Kommentar*</label><br>
                        <textarea id="comment" name="comment" rows="4" cols="50" required class="form-textarea"></textarea>
                    </div>

                    <div class="form-group">
                        <input type="submit" name="comment" value="Senden" class="btn-submit" id="comment-submit">
                        <span id="sending-status" class="blink" style="display:none;">Kommentar wird gesendet…</span>
                    </div>
                </form>
            </section>
        </div>

        <div class="sidebar single">
            <div class="section popular">
                <h2 class="section-title">Popular</h2>
                <?php if (!empty($popular)): ?>
                    <?php foreach ($popular as $p): ?>
                        <div class="post clearfix">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= BASE_URL . '/public/resources/assets/images/' . $e($p['image']); ?>" alt="">
                            <?php endif; ?>
                            <a href="<?= BASE_URL . '/public/single.php?id=' . (int)$p['id']; ?>" class="title">
                                <h4><?= $e($p['title']); ?></h4>
                            </a>
                            <?php if (isset($p['comment_count'])): ?>
                                <small>(<?= (int)$p['comment_count'] ?> Kommentare)</small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><em>Keine beliebten Beiträge vorhanden.</em></p>
                <?php endif; ?>
            </div>

            <div class="section topics">
                <h2 class="section-title">Topics</h2>
                <ul>
                    <?php if (!empty($topics)): ?>
                        <?php foreach ($topics as $topic): ?>
                            <li>
                                <a href="<?= BASE_URL . '/public/index.php?t_id=' . (int)$topic['id'] . '&name=' . urlencode((string)$topic['name']); ?>">
                                    <?= $e($topic['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><em>Keine Topics vorhanden.</em></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>

<?php if ($recaptchaSiteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $e($recaptchaSiteKey) ?>"></script>
    <script>
        // reCAPTCHA v3 Token beim Laden anfordern und ins Hidden-Feld schreiben
        document.addEventListener('DOMContentLoaded', function () {
            grecaptcha.ready(function () {
                grecaptcha.execute('<?= $e($recaptchaSiteKey) ?>', {action: 'submit_comment'})
                    .then(function (token) {
                        var el = document.getElementById('g-recaptcha-response');
                        if (el) el.value = token;
                    });
            });
        });
    </script>
<?php endif; ?>

<script>
    // Reply-Helper: setzt parent_id und scrollt zum Formular
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a.reply');
        if (!a) return;
        e.preventDefault();
        const pid = a.getAttribute('data-parent') || '';
        const input = document.getElementById('parent_id');
        if (input) input.value = pid;
        document.getElementById('comment-form')?.scrollIntoView({behavior: 'smooth'});
    });

    // UI-Feedback beim Absenden (Disable Button + Status anzeigen)
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('comment-form');
        const submitBtn = document.getElementById('comment-submit');
        const statusText = document.getElementById('sending-status');
        if (form && submitBtn && statusText) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.value = 'Senden…';
                statusText.style.display = 'inline';
            });
        }
    });
</script>
</body>
</html>
