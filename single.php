<?php
require 'path.php';                                   // Pfade/URLs (ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/OOP/bootstrap.php';    // OOP-Autoloader

use App\OOP\Repositories\DbRepository;                // DB-Zugriff
use App\OOP\Controllers\PostReadController;           // Post-Reader
use App\OOP\Controllers\CommentController;            // Kommentar-Handling

// ---------------------------------------------------
// Kommentar-POST annehmen (Form postet auf dieselbe Seite)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    (new CommentController(new DbRepository()))->store($_POST); // Redirect erfolgt im Controller
}
// ---------------------------------------------------

// Post-ID aus Query lesen
$id = (int)($_GET['id'] ?? 0);

// ViewModel laden
$ctrl = new PostReadController(new DbRepository());
$vm   = $ctrl->show($id); // ['post'=>…, 'comments'=>…, optional 'posts','topics']

// Daten für Template
$post     = $vm['post'];
$comments = $vm['comments'];
$posts    = $vm['posts']  ?? []; // Popular
$topics   = $vm['topics'] ?? []; // Topics

// Helper für Legacy-Funktion display_comments() bereitstellen
require_once ROOT_PATH . '/app/helpers/comments.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($post['title']); ?> | DHBW-BLOG</title>

    <!-- Fonts/Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* grobes Layout */
        .content { display:flex; gap:20px; flex-wrap:wrap; }
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
                            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        </header>
                        <div class="post-content">
                            <?php echo html_entity_decode($post['body']); ?>
                        </div>
                    </article>
                </section>

                <!-- Comments -->
                <section class="comment-section" id="comments">
                    <?php display_comments((int)$post['id']); ?>

                    <h3 class="comment-title">Kommentar hinzufügen</h3>
                    <form id="comment-form" action="single.php?id=<?php echo (int)$post['id']; ?>" method="post">
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="post_id"  id="post_id"  value="<?php echo (int)$post['id']; ?>">

                        <div class="form-group">
                            <label for="username">Benutzername:</label><br>
                            <input type="text" id="username" name="username" required class="form-input" autocomplete="name">
                        </div>

                        <div class="form-group">
                            <label for="comment">Kommentar:</label><br>
                            <textarea id="comment" name="comment" rows="4" cols="50" required class="form-textarea"></textarea>
                        </div>

                        <div class="form-group">
                            <input type="submit" value="Senden" class="btn-submit">
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
                            <img src="<?php echo BASE_URL . '/assets/images/' . htmlspecialchars($p['image']); ?>" alt="">
                            <a href="<?php echo BASE_URL . '/single.php?id=' . (int)$p['id']; ?>" class="title">
                                <h4><?php echo htmlspecialchars($p['title']); ?></h4>
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
                                <a href="<?php echo BASE_URL . '/index.php?t_id=' . (int)$topic['id'] . '&name=' . urlencode($topic['name']); ?>">
                                    <?php echo htmlspecialchars($topic['name']); ?>
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <!-- Kleines Reply-Helper-Skript -->
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
