<?php
require __DIR__ . '/path.php';
require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Core/DB.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';

use App\Infrastructure\Repositories\DbRepository; // Repo

$db = new DbRepository(); // DI-Repo

$recent     = [];
$postsTitle = 'Aktuelle Beiträge';
$topics     = $db->selectAll('topics', [], 'name ASC'); // Topics laden

// Filter: Topic oder Suche, sonst alle veröffentlichten
if (isset($_GET['t_id'])) {
  $tId   = (int)($_GET['t_id'] ?? 0);
  $tName = (string)($_GET['name'] ?? '');
  $recent = $db->getPostsByTopicId($tId);
  $postsTitle = "Du hast nach folgenden Posts gesucht '" . htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') . "'";
} elseif (!empty($_POST['search-term'])) {
  $term = (string)$_POST['search-term'];
  $postsTitle = "Du hast nach folgenden Posts gesucht '" . htmlspecialchars($term, ENT_QUOTES, 'UTF-8') . "'";
  $recent = $db->searchPosts($term);
} else {
  $recent = $db->getPublishedPosts(); // id,title,body,image,username,created_at
}

$trending = array_slice($recent, 0, 12); // Top-N für Slider

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL; ?>/public/resources/assets/css/style.css?v=5">
  <title>Blog</title>
</head>
<body>

  <?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>
  <?php include(ROOT_PATH . "/app/Support/includes/messages.php"); ?>

  <div class="page-wrapper">

  <!-- HERO: Study Abroad -->
<section class="experience-section">
  <h2 class="unterstrichene-ueberschrift">
    Auslandssemester – alles, was du wissen musst
  </h2>
  <p>
    Finde Destinationen, Erfahrungsberichte, Budgets & Visatipps. 
    Von Studierenden für Studierende.
  </p>
</section>

       <!-- Slider -->
    <div class="post-slider">
      <h1 class="slider-title">Angesagte Beiträge</h1>
      <i class="fas fa-chevron-left prev"></i>
      <i class="fas fa-chevron-right next"></i>

      <div class="post-wrapper">
        <?php foreach ($trending as $post): ?>
          <?php
            $img         = (string)($post['image'] ?? '');
            $title       = (string)($post['title'] ?? '');
            $username    = (string)($post['username'] ?? '');
            $createdAt   = (string)($post['created_at'] ?? '');
            $createdHuman= $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
          ?>
          <div class="post">
            <?php if ($img !== ''): ?>
              <img
                src="<?= BASE_URL . '/public/resources/assets/images/' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
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

    <!-- Main -->
    <div class="content clearfix">

      <!-- Posts -->
      <div class="main-content">
        <h1 class="recent-post-title"><?= htmlspecialchars((string)$postsTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

        <?php foreach ($recent as $post): ?>
          <?php
            $img         = (string)($post['image'] ?? '');
            $title       = (string)($post['title'] ?? '');
            $username    = (string)($post['username'] ?? '');
            $createdAt   = (string)($post['created_at'] ?? '');
            $createdHuman= $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
            $body        = (string)($post['body'] ?? '');
            $preview     = mb_strlen($body) > 150 ? mb_substr($body, 0, 150) . '...' : $body;
          ?>
          <div class="post clearfix">
            <?php if ($img !== ''): ?>
              <img
                src="<?= BASE_URL . '/public/resources/assets/images/' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
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
              <a href="single.php?id=<?= (int)($post['id'] ?? 0); ?>" class="btn read-more">Weiterlesen</a>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <div class="sidebar">
        <div class="section search">
          <h2 class="section-title">Suchen</h2>
          <form action="index.php" method="post">
            <input type="text" name="search-term" class="text-input" placeholder="Suchen...">
          </form>
        </div>

        <div class="section topics">
          <h2 class="section-title">Themen</h2>
          <ul>
            <?php foreach ($topics as $topic): ?>
              <li>
                <a href="<?= BASE_URL . '/public/index.php?t_id='.(int)$topic['id'].'&name='.urlencode((string)$topic['name']); ?>">
                  <?= htmlspecialchars((string)$topic['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

    </div>
  </div>

  <?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="<?= BASE_URL; ?>/public/resources/assets/js/scripts.js?v=5"></script>
</body>
</html>


