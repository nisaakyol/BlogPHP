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

  <style>
    /* Grund-Hintergrund: Sand */
    html, body {
      background: #efe7dd !important; /* Sand-Ton */
      padding: 0 !important;
      margin: 0 !important;
    }

    /* Sand auch für Wrapper */
    html,
    body,
    .page-wrapper,
    .content,
    .main-content,
    .sidebar {
      background: #efe7dd !important;
    }

    /* Abstand nach oben kleiner machen (überschreibt style.css) */
    .content {
      width: 90%;
      margin: 40px auto 80px; /* statt 150px */
    }

    /* Banner im gleichen Stil/Breite wie die Karten */
    .experience-section {
      width: 90%;
      margin: 40px auto 0;  /* Abstand unter dem Header */
      padding: 40px 55px;

      background: #2e3a46;  /* blau wie Header */
      color: #efe7dd;
      text-align: center;

      border-radius: 22px;
      box-shadow: 0 18px 45px rgba(0,0,0,.18);
    }

    .experience-section h2 {
      font-size: 36px;
      margin: 0 0 12px;
      color: #ffffff;
      letter-spacing: 0.5px;
    }

    .experience-section p {
      font-size: 17px;
      margin: 0;
      opacity: .92;
    }

    /* Unterstreichung im Banner dezent in weiß */
    .experience-section .unterstrichene-ueberschrift {
      position: relative;
      display: inline-block;
      padding-bottom: 8px;
    }

    .experience-section .unterstrichene-ueberschrift::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      height: 2px;
      background: rgba(255,255,255,.8);
    }

    /* Slider optisch auf Sand */
    .post-slider {
      background: #efe7dd !important;  /* gleicher Sand-Ton */
      padding-top: 25px;
      padding-bottom: 25px;
    }

    /* Slider-Karten bleiben weiß, leicht abgerundet */
    .post-slider .post-wrapper .post {
      background: #ffffff !important;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,.06);
    }

    /* Sidebar-Karten */
    .sidebar .section {
      background: #ffffff !important;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,.06);
      border: 1px solid rgba(0,0,0,.04);
    }

    /* Posts (Liste) als Cards */
    .content .main-content:not(.single) .post {
      background: #ffffff !important;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,.06);
    }
  </style>
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
            $img          = (string)($post['image'] ?? '');
            $title        = (string)($post['title'] ?? '');
            $username     = (string)($post['username'] ?? '');
            $createdAt    = (string)($post['created_at'] ?? '');
            $createdHuman = $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
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

      <!-- Sidebar zuerst (links) -->
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

      <!-- Posts (rechts) -->
      <div class="main-content">
        <h1 class="recent-post-title"><?= htmlspecialchars((string)$postsTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

        <?php foreach ($recent as $post): ?>
          <?php
            $img          = (string)($post['image'] ?? '');
            $title        = (string)($post['title'] ?? '');
            $username     = (string)($post['username'] ?? '');
            $createdAt    = (string)($post['created_at'] ?? '');
            $createdHuman = $createdAt ? date('F j, Y', strtotime($createdAt)) : '';
            $body         = (string)($post['body'] ?? '');
            $preview      = mb_strlen($body) > 150 ? mb_substr($body, 0, 150) . '...' : $body;
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

    </div>
  </div>

  <?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="<?= BASE_URL; ?>/public/resources/assets/js/scripts.js?v=5"></script>
</body>
</html>
