<?php
require 'path.php'; // Lädt Projektpfade/URLs (z. B. ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap.php'; // Autoload/Bootstrap der OOP-Schicht (Namespaces, Services, Repos)

use App\OOP\Repositories\DbRepository;

$db = new DbRepository();

// ─────────────────────────────────────────────────────────────────────
// Daten laden
// ─────────────────────────────────────────────────────────────────────
$posts      = [];
$postsTitle = 'Recent Posts';
$topics     = $db->selectAll('topics', [], 'name ASC');

if (isset($_GET['t_id'])) {
  $tId   = (int)($_GET['t_id'] ?? 0);
  $tName = (string)($_GET['name'] ?? '');
  $posts = $db->getPostsByTopicId($tId);
  $postsTitle = "You searched for posts under '" . htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') . "'";
} elseif (!empty($_POST['search-term'])) {
  $term = (string)$_POST['search-term'];
  $postsTitle = "You searched for '" . htmlspecialchars($term, ENT_QUOTES, 'UTF-8') . "'";
  $posts = $db->searchPosts($term); // deine erweiterte Suche
} else {
  $posts = $db->getPublishedPosts();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <!-- Externe Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
  <!-- Webfonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
  <!-- Projekt-CSS -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
  <title>Blog</title>
</head>
<body>

  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>
  <?php include(ROOT_PATH . "/app/includes/messages.php"); ?>

  <div class="page-wrapper">

    <!-- Slider mit ausgewählten/trendenden Posts -->
    <div class="post-slider">
      <h1 class="slider-title">Trending Posts</h1>
      <i class="fas fa-chevron-left prev"></i>
      <i class="fas fa-chevron-right next"></i>

      <div class="post-wrapper">
        <?php foreach ($posts as $post): ?>
          <?php
            $img = (string)($post['image'] ?? '');
            $title = (string)($post['title'] ?? '');
            $username = (string)($post['username'] ?? '');
            $createdAt = (string)($post['created_at'] ?? '');
            $createdHuman = $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
          ?>
          <div class="post">
            <?php if ($img !== ''): ?>
              <img
                src="<?= BASE_URL . '/assets/images/' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                alt=""
                class="slider-image">
            <?php endif; ?>
            <div class="post-info">
              <h4>
                <a href="single.php?id=<?= (int)($post['id'] ?? 0); ?>">
                  <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </h4>
              <i class="far fa-user"> <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></i>
              &nbsp;
              <?php if ($createdHuman !== ''): ?>
                <i class="far fa-calendar"> <?= htmlspecialchars($createdHuman, ENT_QUOTES, 'UTF-8'); ?></i>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Haupt-Content-Bereich -->
    <div class="content clearfix">

      <!-- Postliste -->
      <div class="main-content">
        <h1 class="recent-post-title"><?= htmlspecialchars((string)$postsTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

        <?php foreach ($posts as $post): ?>
          <?php
            $img = (string)($post['image'] ?? '');
            $title = (string)($post['title'] ?? '');
            $username = (string)($post['username'] ?? '');
            $createdAt = (string)($post['created_at'] ?? '');
            $createdHuman = $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
            $body = (string)($post['body'] ?? '');
            $preview = mb_strlen($body) > 150 ? mb_substr($body, 0, 150) . '...' : $body;
          ?>
          <div class="post clearfix">
            <?php if ($img !== ''): ?>
              <img
                src="<?= BASE_URL . '/assets/images/' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                alt=""
                class="post-image">
            <?php endif; ?>

            <div class="post-preview">
              <h2>
                <a href="single.php?id=<?= (int)($post['id'] ?? 0); ?>">
                  <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </h2>
              <i class="far fa-user"> <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></i>
              &nbsp;
              <?php if ($createdHuman !== ''): ?>
                <i class="far fa-calendar"> <?= htmlspecialchars($createdHuman, ENT_QUOTES, 'UTF-8'); ?></i>
              <?php endif; ?>
              <p class="preview-text">
                <?= html_entity_decode($preview); ?>
              </p>
              <a href="single.php?id=<?= (int)($post['id'] ?? 0); ?>" class="btn read-more">Read More</a>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Sidebar: Suche + Topics -->
      <div class="sidebar">
        <div class="section search">
          <h2 class="section-title">Search</h2>
          <form action="index.php" method="post">
            <input type="text" name="search-term" class="text-input" placeholder="Search...">
          </form>
        </div>

        <div class="section topics">
          <h2 class="section-title">Topics</h2>
          <ul>
            <?php foreach ($topics as $topic): ?>
              <li>
                <a href="<?= BASE_URL . '/index.php?t_id='.(int)$topic['id'].'&name='.urlencode((string)$topic['name']); ?>">
                  <?= htmlspecialchars((string)$topic['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

    </div>
  </div>

  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="<?= BASE_URL; ?>/assets/js/scripts.js"></script>
</body>
</html>
