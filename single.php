<?php
declare(strict_types=1);

require 'path.php';                                              // Pfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap.php';          // OOP-Autoloader, Session, Helpers

use App\OOP\Repositories\DbRepository;
use App\OOP\Controllers\PostReadController;
use App\OOP\Controllers\CommentController;

// ---------------------------------------------------
// Kommentar absenden (Form postet auf dieselbe Seite)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    // Der CommentController validiert zusätzlich (CSRF, Honeypot, reCAPTCHA, etc.) und macht Redirect
    (new CommentController(new DbRepository()))->store($_POST);
    exit; // Safety
}
// ---------------------------------------------------

// Post-ID aus Query lesen
$id = (int)($_GET['id'] ?? 0);

// ViewModel laden
$repo = new DbRepository();
$ctrl = new PostReadController($repo);
$vm   = $ctrl->show($id); // ['post'=>…, 'comments'=>…, optional 'posts','topics']

// Daten für Template
$post     = $vm['post'];
$comments = $vm['comments'];
$posts    = $vm['posts']  ?? []; // Popular
$topics   = $vm['topics'] ?? []; // Topics

// Helper für Legacy-Funktion display_comments() bereitstellen
require_once ROOT_PATH . '/app/helpers/comments.php';

// weitere Helper (CSRF + Cookies)
require_once ROOT_PATH . '/app/helpers/csrf.php';
require_once ROOT_PATH . '/app/helpers/cookies.php';

// kleine Helper
$isLoggedIn = function (): bool {
    return !empty($_SESSION['id']);
};
$currentUsername = $_SESSION['username'] ?? 'user';

// Cookie-Prefill nur für Gäste (nur Name)
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

// reCAPTCHA v3 Site-Key aus ENV (aus path.php)
$recaptchaSiteKey = getenv('RECAPTCHA_V3_SITE') ?: getenv('RECAPTCHA_SITE') ?: '';

// Bild-URL vorbereiten (falls vorhanden)
$heroImgUrl = !empty($post['image'])
  ? BASE_URL . '/assets/images/' . rawurlencode((string)$post['image'])
  : null;

// kleiner Escaper
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $e($post['title'] ?? 'Beitrag'); ?> | DHBW-BLOG</title>

    <!-- Fonts/Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Single-Overrides: verhindern Cutoff in der Einzelansicht -->
    <style>
      .main-content.single article.post{
        height:auto !important;
        overflow:visible !important;
        background:transparent;
        box-shadow:none;
      }
      .main-content.single .post-hero{ margin:0 0 16px; overflow:hidden; line-height:0 }
      .main-content.single .post-hero img{ display:block; width:100%; height:140px; object-fit:cover }
      .main-content.single .post-textwrap{
        overflow:visible !important;
        max-width:100%;
      }
      .main-content.single .post-textwrap *{
        max-width:100%;
        word-break:break-word;
        overflow-wrap:anywhere;
      }
    </style>
</head>
<body>

    <!-- Header -->
    <?php include(ROOT_PATH . "/app/includes/header.php"); ?>

    <!-- Page Wrapper -->
    <div class="page-wrapper">

        <!-- Content -->
        <div class="content clearfix">

            <!-- Main Content Wrapper -->
            <div class="main-content-wrapper main-content single">

                <!-- Post -->
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

                        <!-- Getrennter Text-Wrapper (schützt vor overflow/height aus Karten-Styles) -->
                        <div class="post-textwrap">
                          <div class="post-content">
                            <?= html_entity_decode((string)$post['body']); ?>
                          </div>
                        </div>
                    </article>
                </section>

                <!-- Comments -->
                <section class="comment-section" id="comments">
                    <?php
                    if (!empty($_SESSION['message'])) {
                        $type = $_SESSION['type'] ?? 'success';
                        echo '<div class="flash '.$type.'">'.$e($_SESSION['message']).'</div>';
                        unset($_SESSION['message'], $_SESSION['type']);
                    }

                    // Kommentare anzeigen
                    display_comments((int)$post['id']);
                    ?>

                    <h3 class="comment-title">Kommentar hinzufügen</h3>

                    <form id="comment-form" action="single.php?id=<?= (int)$post['id']; ?>" method="post" class="comment-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="post_id"  id="post_id"  value="<?= (int)$post['id']; ?>">

                        <!-- reCAPTCHA v3 -->
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <input type="hidden" name="recaptcha_action" value="submit_comment">

                        <!-- Honeypot -->
                        <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
                            <label>Dein Name (frei lassen)</label>
                            <input type="text" name="hp_name" autocomplete="off" tabindex="-1">
                        </div>

                        <?php if ($isLoggedIn()): ?>
                            <p class="muted">Eingeloggt als <strong><?= $e($currentUsername); ?></strong></p>
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
                            <input type="submit" value="Senden" class="btn-submit" id="comment-submit">
                            <span id="sending-status" class="blink" style="display:none;">Kommentar wird gesendet…</span>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="sidebar single">

                <!-- Popular -->
                <div class="section popular">
                    <h2 class="section-title">Popular</h2>
                    <?php foreach ($posts as $p): ?>
                        <div class="post clearfix">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= BASE_URL . '/assets/images/' . $e($p['image']); ?>" alt="">
                            <?php endif; ?>
                            <a href="<?= BASE_URL . '/single.php?id=' . (int)$p['id']; ?>" class="title">
                                <h4><?= $e($p['title']); ?></h4>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Topics -->
                <div class="section topics">
                    <h2 class="section-title">Topics</h2>
                    <ul>
                        <?php foreach ($topics as $topic): ?>
                            <li>
                                <a href="<?= BASE_URL . '/index.php?t_id=' . (int)$topic['id'] . '&name=' . urlencode((string)$topic['name']); ?>">
                                    <?= $e($topic['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <!-- reCAPTCHA v3 Script -->
    <?php if ($recaptchaSiteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $e($recaptchaSiteKey) ?>"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        grecaptcha.ready(function() {
          grecaptcha.execute('<?= $e($recaptchaSiteKey) ?>', {action: 'submit_comment'})
            .then(function(token) {
              var el = document.getElementById('g-recaptcha-response');
              if (el) el.value = token;
            });
        });
      });
    </script>
    <?php endif; ?>

    <!-- Reply-Helper -->
    <script>
    document.addEventListener('click', function (e) {
      const a = e.target.closest('a.reply');
      if (!a) return;
      e.preventDefault();
      const pid = a.getAttribute('data-parent') || '';
      const input = document.getElementById('parent_id');
      if (input) input.value = pid;
      document.getElementById('comment-form')?.scrollIntoView({behavior: 'smooth'});
    });
    </script>

    <!-- UX: Senden-Button sperren -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('comment-form');
      const submitBtn = document.getElementById('comment-submit');
      const statusText = document.getElementById('sending-status');

      if (form && submitBtn && statusText) {
        form.addEventListener('submit', function() {
          submitBtn.disabled = true;
          submitBtn.value = 'Senden…';
          statusText.style.display = 'inline';
        });
      }
    });
    </script>
</body>
</html>
