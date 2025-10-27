<?php
declare(strict_types=1);

require 'path.php';                                              // Pfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap_once.php';     // OOP-Autoloader, Session, Helpers

use App\OOP\Repositories\DbRepository;
use App\OOP\Controllers\PostReadController;
use App\OOP\Controllers\CommentController;

// ---------------------------------------------------
// Kommentar absenden (Form postet auf dieselbe Seite)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    // Der CommentController validiert zusätzlich (usersOnly, etc.)
    (new CommentController(new DbRepository()))->store($_POST); // macht Redirect
    exit; // safety (sollte durch Redirect ohnehin nicht erreicht werden)
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

// kleine Helper
$isLoggedIn = function (): bool {
    return !empty($_SESSION['id']);
};
$currentUsername = $_SESSION['username'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($post['title'] ?? 'Beitrag'); ?> | DHBW-BLOG</title>

    <!-- Fonts/Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .content { display:flex; gap:20px; flex-wrap:wrap; }
        .comment-section .form-group { margin: .75rem 0; }
        .comment-section .btn-submit { padding: .5rem 1rem; }
        .muted { color:#666; font-size:.95rem; }
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
            <div class="main-content-wrapper">

                <!-- Post -->
                <section class="post-section">
                    <article class="post">
                        <header class="post-header">
                            <h1 class="post-title"><?= htmlspecialchars($post['title']); ?></h1>
                        </header>
                        <div class="post-content">
                            <?= html_entity_decode($post['body']); ?>
                        </div>
                    </article>
                </section>

                <!-- Comments -->
                <section class="comment-section" id="comments">
                    <?php display_comments((int)$post['id']); ?>

                    <h3 class="comment-title">Kommentar hinzufügen</h3>

                    <?php if ($isLoggedIn()): ?>
                        <form id="comment-form" action="single.php?id=<?= (int)$post['id']; ?>" method="post">
                            <input type="hidden" name="parent_id" id="parent_id" value="">
                            <input type="hidden" name="post_id"  id="post_id"  value="<?= (int)$post['id']; ?>">
                            <!-- Username NICHT editierbar: nur anzeigen, Wert kommt im Controller aus der Session -->
                            <p class="muted">Eingeloggt als <strong><?= htmlspecialchars($currentUsername, ENT_QUOTES, 'UTF-8'); ?></strong></p>

                            <div class="form-group">
                                <label for="comment">Kommentar:</label><br>
                                <textarea id="comment" name="comment" rows="4" cols="50" required class="form-textarea"></textarea>
                            </div>

                            <div class="form-group">
                                <input type="submit" value="Senden" class="btn-submit">
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Bitte <a href="<?= BASE_URL ?>/login.php">einloggen</a>, um einen Kommentar zu schreiben.</p>
                    <?php endif; ?>
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
                                <img src="<?= BASE_URL . '/assets/images/' . htmlspecialchars($p['image']); ?>" alt="">
                            <?php endif; ?>
                            <a href="<?= BASE_URL . '/single.php?id=' . (int)$p['id']; ?>" class="title">
                                <h4><?= htmlspecialchars($p['title']); ?></h4>
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
                                <a href="<?= BASE_URL . '/index.php?t_id=' . (int)$topic['id'] . '&name=' . urlencode($topic['name']); ?>">
                                    <?= htmlspecialchars($topic['name']); ?>
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

    <!-- Reply-Helper: setzt parent_id und scrollt zum Formular -->
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
</body>
</html>
