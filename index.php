<?php
require 'path.php'; // Lädt Projektpfade/URLs (z. B. ROOT_PATH, BASE_URL)
require_once ROOT_PATH . '/app/includes/bootstrap_once.php'; // Autoload/Bootstrap der OOP-Schicht (Namespaces, Services, Repos)
require_once ROOT_PATH . '/app/database/connect.php'; // stellt $conn für LegacyDB sicher

use App\OOP\Repositories\DbRepository; // OOP-Repository mit DB-Methoden (selectAll, getPublishedPosts, ...)

$db = new DbRepository(); // Repository-Instanz für DB-Zugriffe

$posts      = [];                  // Container für anzuzeigende Posts
$postsTitle = 'Recent Posts';      // Standardüberschrift für die Liste
$topics     = $db->selectAll('topics', [], 'name ASC'); // Alle Topics (für Sidebar), alphabetisch

// Search / Filter Logik wie früher
if (isset($_GET['t_id'])) {
  // Filter nach Topic-ID: holt Posts zu einem Topic
  $tId   = (int)($_GET['t_id'] ?? 0);   // robuste Typisierung/Whitelisting via (int)
  $tName = $_GET['name'] ?? '';         // Topic-Name aus Query (nur zur Anzeige)
  $posts = $db->getPostsByTopicId($tId);
  // Titel mit sicher ausgegebenem Topic-Namen
  $postsTitle = "You searched for posts under '" . htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') . "'";
} elseif (!empty($_POST['search-term'])) {
  // Volltextsuche: wenn ein Suchbegriff gepostet wurde
  $term = (string)$_POST['search-term'];
  $postsTitle = "You searched for '" . htmlspecialchars($term, ENT_QUOTES, 'UTF-8') . "'";
  $posts = $db->searchPosts($term);
} else {
  // Default: alle veröffentlichten Posts
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
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <!-- Webfonts -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">
  <!-- Projekt-CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <title>Blog</title>
</head>
<body>

  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>   <!-- Globale Navigation -->
  <?php include(ROOT_PATH . "/app/includes/messages.php"); ?> <!-- Flash-/Status-Meldungen -->

  <div class="page-wrapper">

    <!-- Slider mit ausgewählten/trendenden Posts -->
    <div class="post-slider">
      <h1 class="slider-title">Trending Posts</h1>
      <i class="fas fa-chevron-left prev"></i>
      <i class="fas fa-chevron-right next"></i>

      <div class="post-wrapper">
        <?php foreach ($posts as $post): ?>
          <div class="post">
            <!-- Slider-Bild; Dateiname aus DB sicher ausgegeben -->
            <img src="<?php echo BASE_URL . '/assets/images/' . htmlspecialchars($post['image']); ?>" alt="" class="slider-image">
            <div class="post-info">
              <h4>
                <!-- Detailseite per ID (gecastet) -->
                <a href="single.php?id=<?php echo (int)$post['id']; ?>">
                  <?php echo htmlspecialchars($post['title']); ?>
                </a>
              </h4>
              <i class="far fa-user"> <?php echo htmlspecialchars($post['username']); ?></i>
              &nbsp;
              <i class="far fa-calendar"> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></i>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Haupt-Content-Bereich -->
    <div class="content clearfix">

      <!-- Postliste -->
      <div class="main-content">
        <h1 class="recent-post-title"><?php echo $postsTitle; ?></h1>

        <?php foreach ($posts as $post): ?>
          <div class="post clearfix">
            <!-- Listenbild -->
            <img src="<?php echo BASE_URL . '/assets/images/' . htmlspecialchars($post['image']); ?>" alt="" class="post-image">
            <div class="post-preview">
              <h2>
                <a href="single.php?id=<?php echo (int)$post['id']; ?>">
                  <?php echo htmlspecialchars($post['title']); ?>
                </a>
              </h2>
              <i class="far fa-user"> <?php echo htmlspecialchars($post['username']); ?></i>
              &nbsp;
              <i class="far fa-calendar"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></i>
              <p class="preview-text">
                <?php
                  // Kurzvorschau: body anreißen, HTML-Entities dekodieren (kompatibel zum Legacy-Ausgabestil)
                  echo html_entity_decode(substr($post['body'], 0, 150) . '...');
                ?>
              </p>
              <a href="single.php?id=<?php echo (int)$post['id']; ?>" class="btn read-more">Read More</a>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Sidebar: Suche + Topics -->
      <div class="sidebar">
        <div class="section search">
          <h2 class="section-title">Search</h2>
          <!-- POST-basierte Suche; Feldname 'search-term' wird oben ausgewertet -->
          <form action="index.php" method="post">
            <input type="text" name="search-term" class="text-input" placeholder="Search...">
          </form>
        </div>

        <div class="section topics">
          <h2 class="section-title">Topics</h2>
          <ul>
            <?php foreach ($topics as $topic): ?>
              <li>
                <!-- Link setzt t_id + name; name per urlencode, Anzeige per htmlspecialchars in Titel -->
                <a href="<?php echo BASE_URL . '/index.php?t_id='.(int)$topic['id'].'&name='.urlencode($topic['name']); ?>">
                  <?php echo htmlspecialchars($topic['name']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

    </div>
  </div>

  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?> <!-- Globale Fußzeile -->

  <!-- JS: jQuery, Slider, Projekt-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="assets/js/scripts.js"></script>
</body>
</html>
