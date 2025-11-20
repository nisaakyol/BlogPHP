<?php
declare(strict_types=1);
// Zweck: Rendert die Single-Post-Seite inkl. Bild (mit ALT/Caption),
// Kommentarformular, Popular- & Topics-Sidebar sowie Vorlese-Funktion.

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

// ViewModel über SingleController aufbauen (liefert $post, $topics, $popular)
$sc = new SingleController();
$sc->boot();
$post    = $sc->post ?? [];
$topics  = $sc->topics ?? [];
$popular = $sc->popular ?? [];

// Helper laden (Kommentare rendern, CSRF, Cookies)
require_once ROOT_PATH . '/app/Support/helpers/comments.php';
require_once ROOT_PATH . '/app/Support/helpers/csrf.php';
require_once ROOT_PATH . '/app/Support/helpers/cookies.php';

// kleine Helper
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Login-Check
$isLoggedIn = static function (): bool {
    return !empty($_SESSION['id']);
};

// Username
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

// Bilddaten
$heroImgUrl = !empty($post['image'])
    ? BASE_URL . '/public/resources/assets/images/' . rawurlencode((string)$post['image'])
    : null;
$imgAlt = trim((string)($post['image_alt'] ?? ''));
$imgCap = trim((string)($post['image_caption'] ?? ''));
if ($imgAlt === '') $imgAlt = (string)($post['title'] ?? 'Bild');

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
        /* Hintergrund: Warm Sand */
        html,
        body,
        .page-wrapper {
            background: #f3ede3 !important;
        }

        /* Abstand nach oben reduzieren */
        .page-wrapper {
            margin-top: 0 !important;
            padding-top: 16px;
        }

        .content {
            width: 90%;
            margin: 20px auto 80px;
        }

        /* Single-Ansicht */
        .main-content.single article.post {
            height:auto!important;
            overflow:visible!important;
            background:transparent;
            box-shadow:none;
        }
        .main-content.single .post-hero {
            margin:0 0 16px;
            overflow:hidden;
            line-height:0;
        }
        .main-content.single .post-hero img {
            display:block;
            width:100%;
            height:140px;
            object-fit:cover;
        }
        .post-figcaption {
            font-size:.9rem;
            color:#bfbfbf;
            margin-top:.5rem;
            line-height:1.3;
        }
        .main-content.single .post-textwrap {
            overflow:visible!important;
            max-width:100%;
        }
        .main-content.single .post-textwrap * {
            max-width:100%;
            word-break:break-word;
            overflow-wrap:anywhere;
        }

        .sidebar.single .section .section-title { font-weight:700; }
        .sidebar.single .popular .post.clearfix { margin-bottom:.75rem; }
        .sidebar.single .popular img {
            width:64px;
            height:48px;
            object-fit:cover;
            margin-right:.5rem;
            float:left;
        }
        .sidebar.single .popular h4 {
            margin:0;
            font-size:.95rem;
            line-height:1.2;
        }
        .sidebar.single .popular small { color:#777; display:block; }

        /* ==== HEADERBALKEN & TITEL ==== */
        .main-content.single .post-header {
            background: #2e3a46 !important;  /* dein Blau */
            color: #ffffff;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            box-shadow: none !important;
            margin-bottom: 1rem;
            text-align: center;
        }
        .main-content.single .post-title {
            margin: 0 0 0.5rem;
            font-size: 1.4rem;
            background: transparent !important;
            color: #ffffff !important;
        }

        /* Vorlesen-Controls */
        .skip-link {
            position:absolute;
            left:-9999px;
            top:auto;
            width:1px;
            height:1px;
            overflow:hidden;
        }
        .skip-link:focus {
            position:static;
            width:auto;
            height:auto;
            padding:.4rem .6rem;
            background:#fff;
        }
        .tts-controls {
            display:flex !important;
            gap:.4rem;
            margin:.4rem 0 0.1rem;
            flex-wrap: wrap;
            justify-content:center;
        }
        .tts-controls button{
            all: unset;
            display:inline-block !important;
            padding:.3rem .65rem;
            border-radius: 999px;
            cursor:pointer;
            font:inherit;
            font-size: .9rem;
            line-height:1.2;
            background:#2e3a46 !important;   /* gleiches Blau */
            color:#ffffff !important;
            border:1px solid #1f2930;
        }
        .tts-controls button[disabled]{
            opacity:0.5;
            cursor: default;
        }
        .tts-controls button:not([disabled]):hover{
            filter:brightness(1.1);
        }

        /* ===== Kommentarsektion: Card-Layout ===== */

        .comment-section {
            margin-top: 3rem;
        }

        .comment-section-inner {
            background: #ffffff;
            border-radius: 18px;
            padding: 2rem 2.5rem;
            max-width: 720px;
            margin: 0 auto 3rem;
            box-shadow: 0 18px 45px rgba(0,0,0,.06);
            border: 1px solid rgba(0,0,0,.03);
        }

        .comment-title {
            font-size: 1.6rem;
            margin: 0 0 1.5rem;
        }

        .comment-section p.no-comments {
            margin-bottom: 1.5rem;
            color: #666;
        }

        /* Formular-Gruppen */
        .comment-form .form-group {
            margin-bottom: 1.2rem;
        }

        .comment-form label {
            display: block;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        /* Inputs & Textarea */
        .comment-form input[type="text"],
        .comment-form textarea {
            width: 100%;
            padding: .65rem .9rem;
            border-radius: 10px;
            border: 1px solid #ddd;
            font: inherit;
            box-sizing: border-box;
        }

        .comment-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        /* Name merken – Checkbox hübsch nebeneinander */
        .comment-form .remember-wrapper {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .95rem;
            color: #333;
        }

        .comment-form .remember-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        /* Senden-Button */
        .btn-submit {
            padding: .5rem 1.4rem;
            border-radius: 999px;
            border: none;
            background: #2e3a46;
            color: #ffffff;
            font: inherit;
            cursor: pointer;
            transition: background-color .25s ease, transform .1s ease;
        }

        .btn-submit:hover {
            background: #1f2831;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Status-Text unter dem Button */
        #sending-status {
            margin-left: .75rem;
            font-size: .9rem;
            color: #555;
        }

        /* Kommentar-Liste als „Bubbles“ */
        .comment-section-inner > ul,
        .comment-section-inner ul.comments,
        .comment-section-inner ul {
            list-style: none;
            margin: 0 0 1.8rem;
            padding: 0;
        }

        .comment-section-inner ul > li {
            background: #fafafa;
            border-radius: 12px;
            border: 1px solid #e5e5e5;
            padding: .75rem 1rem .8rem;
            margin-bottom: .9rem;
            font-size: .97rem;
            line-height: 1.5;
        }

        .comment-section-inner ul > li strong {
            font-weight: 700;
            margin-right: .35rem;
        }

        .comment-section-inner a.reply {
            display: inline-block;
            margin-top: .35rem;
            font-size: .9rem;
            color: #2e3a46;
            font-weight: 600;
        }

        .comment-section-inner a.reply:hover {
            text-decoration: underline;
        }

        .comment-section-inner ul ul {
            margin-top: .6rem;
            margin-left: 1.1rem;
            padding-left: .8rem;
            border-left: 2px solid #e3e3e3;
        }

        .comment-section-inner ul ul > li {
            background: #fdfdfd;
        }

        .flash.success {
            margin-bottom: 1rem;
            padding: .7rem 1rem;
            border-radius: 10px;
            border: 1px solid #b3e5c2;
            background: #e4f9ea;
            color: #1b5e20;
            font-size: .95rem;
        }
    </style>
</head>

<body>

<a href="#single-post" class="skip-link">Zum Inhalt springen</a>
<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

<div class="page-wrapper">
    <div class="content clearfix">

        <!-- Sidebar links -->
        <aside class="sidebar single" aria-label="Zusätzliche Inhalte">
            <div class="section popular">
                <h2 class="section-title">Popular</h2>
                <?php if (!empty($popular)): ?>
                    <?php foreach ($popular as $p): ?>
                        <div class="post clearfix">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= BASE_URL . '/public/resources/assets/images/' . $e($p['image']); ?>"
                                     alt="<?= $e((string)($p['image_alt'] ?? $p['title'] ?? 'Vorschaubild')) ?>">
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
        </aside>

        <!-- Hauptinhalt rechts -->
        <div class="main-content-wrapper main-content single">

            <section class="post-section">
                <article class="post" id="single-post" aria-labelledby="post-title">
                    <header class="post-header">
                        <h1 class="post-title" id="post-title"><?= $e($post['title'] ?? ''); ?></h1>

                        <!-- Vorlesen-Steuerung -->
                        <div class="tts-controls" aria-label="Vorlese-Steuerung">
                            <button type="button" id="tts-play"  aria-label="Artikel vorlesen">▶︎ Vorlesen</button>
                            <button type="button" id="tts-pause" aria-label="Wiedergabe pausieren" disabled>⏸︎ Pause</button>
                            <button type="button" id="tts-stop"  aria-label="Wiedergabe stoppen" disabled>⏹︎ Stop</button>
                        </div>
                    </header>

                    <?php if ($heroImgUrl): ?>
                        <figure class="post-hero">
                            <img src="<?= $heroImgUrl; ?>" alt="<?= $e($imgAlt); ?>">
                            <?php if ($imgCap !== ''): ?>
                                <figcaption class="post-figcaption"><?= $e($imgCap); ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>

                    <div class="post-textwrap">
                        <div class="post-content">
                            <?= html_entity_decode((string)($post['body'] ?? '')); ?>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Kommentarbereich -->
            <section class="comment-section" id="comments" aria-labelledby="comments-title">
                <h2 id="comments-title" class="visually-hidden" style="position:absolute;left:-9999px">Kommentare</h2>

                <div class="comment-section-inner">
                    <?php
                    if (!empty($_SESSION['message'])) {
                        $type = $_SESSION['type'] ?? 'success';
                        echo '<div class="flash ' . $type . '" role="alert">' . $e($_SESSION['message']) . '</div>';
                        unset($_SESSION['message'], $_SESSION['type']);
                    }

                    if (!empty($post['id'])) {
                        ob_start();
                        display_comments((int)$post['id']);
                        $commentsHtml = trim(ob_get_clean());

                        if ($commentsHtml === '') {
                            echo '<p class="no-comments"><em>Keine Kommentare vorhanden.</em></p>';
                        } else {
                            echo $commentsHtml;
                        }
                    }
                    ?>

                    <h3 class="comment-title">Kommentar hinzufügen</h3>

                    <!-- Kommentarformular -->
                    <form
                        id="comment-form"
                        action="<?= BASE_URL ?>/public/single.php?id=<?= (int)($post['id'] ?? 0); ?>"
                        method="post"
                        class="comment-form"
                        novalidate
                    >
                        <!-- CSRF-Token -->
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <!-- Eltern-Kommentar-ID -->
                        <input type="hidden" name="parent_id" id="parent_id" value="">

                        <!-- Ziel-Post-ID -->
                        <input type="hidden" name="post_id" id="post_id" value="<?= (int)($post['id'] ?? 0); ?>">

                        <!-- reCAPTCHA Felder -->
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <input type="hidden" name="recaptcha_action" value="submit_comment">

                        <!-- Honeypot -->
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
                                <label for="author_name">Name*</label>
                                <input id="author_name" name="author_name" type="text" value="<?= $prefillName ?>" required aria-required="true">
                            </div>
                            <div class="form-group">
                                <label class="remember-wrapper">
                                    <input type="checkbox" name="remember_author" value="1" <?= ($prefillName ? 'checked' : ''); ?>>
                                    <span>Name merken</span>
                                </label>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="comment">Kommentar*</label>
                            <textarea id="comment" name="comment" required class="form-textarea" aria-required="true"></textarea>
                        </div>

                        <div class="form-group">
                            <input type="submit" name="comment" value="Senden" class="btn-submit" id="comment-submit">
                            <span id="sending-status" class="blink" style="display:none;">Kommentar wird gesendet…</span>
                        </div>
                    </form>
                </div>
            </section>
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
    // reCAPTCHA v3 Token beim Laden anfordern
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof grecaptcha === 'undefined') {
            return;
        }
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
        var a = e.target.closest ? e.target.closest('a.reply') : null;
        if (!a) return;
        e.preventDefault();
        var pid = a.getAttribute('data-parent') || '';
        var input = document.getElementById('parent_id');
        if (input) input.value = pid;
        var formEl = document.getElementById('comment-form');
        if (formEl && formEl.scrollIntoView) {
            formEl.scrollIntoView({behavior: 'smooth'});
        }
    });

    // UI-Feedback beim Absenden
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('comment-form');
        var submitBtn = document.getElementById('comment-submit');
        var statusText = document.getElementById('sending-status');
        if (form && submitBtn && statusText) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.value = 'Senden…';
                statusText.style.display = 'inline';
            });
        }
    });

    // Vorlesen (Web Speech API)
    (function(){
        if (!('speechSynthesis' in window)) return;

        var playBtn  = document.getElementById('tts-play');
        var pauseBtn = document.getElementById('tts-pause');
        var stopBtn  = document.getElementById('tts-stop');

        function getArticleText() {
            var titleEl   = document.getElementById('post-title');
            var articleEl = document.querySelector('#single-post .post-content');
            var imgEl     = document.querySelector('.post-hero img');
            var capEl     = document.querySelector('.post-figcaption');

            var title   = titleEl ? (titleEl.innerText || '') : '';
            var body    = articleEl ? (articleEl.innerText || '') : '';

            var caption = capEl ? (capEl.innerText || '') : '';
            var alt     = imgEl ? (imgEl.getAttribute('alt') || '') : '';
            var imgDesc = (caption || alt);

            var parts = [];
            if (title.trim())   parts.push(title.trim());
            if (imgDesc.trim()) parts.push(imgDesc.trim());
            if (body.trim())    parts.push(body.trim());

            if (!parts.length) return '';

            return parts
                .map(function(t){ return t.replace(/\s+/g, ' ').replace(/\s*\.\s*$/, ''); })
                .join('. ') + '.';
        }

        function setState(s) {
            if (!playBtn || !pauseBtn || !stopBtn) return;

            if (s === 'playing') {
                playBtn.disabled  = true;
                pauseBtn.disabled = false;
                stopBtn.disabled  = false;
            } else if (s === 'paused') {
                playBtn.disabled  = false;
                pauseBtn.disabled = false;
                stopBtn.disabled  = false;
            } else {
                playBtn.disabled  = false;
                pauseBtn.disabled = true;
                stopBtn.disabled  = true;
            }
        }
        setState('idle');

        var utter = null;

        if (playBtn) {
            playBtn.addEventListener('click', function () {
                if (speechSynthesis.paused) {
                    speechSynthesis.resume();
                    setState('playing');
                    return;
                }
                if (speechSynthesis.speaking) {
                    speechSynthesis.cancel();
                }

                var txt = getArticleText();
                if (!txt) return;

                utter = new SpeechSynthesisUtterance(txt);
                utter.lang   = 'de-DE';
                utter.rate   = 1.0;
                utter.pitch  = 1.0;
                utter.volume = 1.0;
                utter.onend   = function () { setState('idle'); };
                utter.onerror = function () { setState('idle'); };

                speechSynthesis.speak(utter);
                setState('playing');
            });
        }

        if (pauseBtn) {
            pauseBtn.addEventListener('click', function () {
                if (speechSynthesis.speaking && !speechSynthesis.paused) {
                    speechSynthesis.pause();
                    setState('paused');
                } else if (speechSynthesis.paused) {
                    speechSynthesis.resume();
                    setState('playing');
                }
            });
        }

        if (stopBtn) {
            stopBtn.addEventListener('click', function () {
                if (speechSynthesis.speaking || speechSynthesis.paused) {
                    speechSynthesis.cancel();
                    setState('idle');
                }
            });
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden && (speechSynthesis.speaking || speechSynthesis.paused)) {
                speechSynthesis.cancel();
                setState('idle');
            }
        });
    })();
</script>

</body>
</html>
