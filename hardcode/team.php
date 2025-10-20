<?php
// Zentrale Pfad-/URL-Konstanten laden (ROOT_PATH, BASE_URL)
require("../path.php");
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Fonts & Styles -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />

  <title>Unser Team</title>

  <style>
    /* Eigenes, unabhängiges Grid: immer 4 Spalten nebeneinander */
    .team-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr); /* genau 4 Spalten */
      gap: 20px;
      align-items: stretch;
    }
    .team-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 6px 16px rgba(0,0,0,.08);
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .team-card img {
      width: 100%;
      height: 180px;            /* einheitliche Höhe */
      object-fit: cover;        /* Bild füllt ohne Verzerren */
      display: block;
    }
    .team-card .container {
      padding: 14px 16px;
      text-align: center;
    }
    .team-card .title {
      color: #888;
      font-size: .95rem;
      margin-top: 4px;
    }

    /* Falls der globale .page-wrapper/.content Styles einschränken,
       stellen wir sicher, dass das Grid die volle Breite nutzen darf. */
    .team-wrapper {
      max-width: 1100px;
      margin: 30px auto 50px;
      padding: 0 16px;
    }

    /* Optional: Auf ganz kleinen Geräten kann das sehr eng werden.
       Wenn du *wirklich immer* 4 nebeneinander willst, lass das so.
       Falls du lieber horizontales Scrollen statt „zu klein“ willst,
       kommentiere den Block oben aus und nutze den folgenden: */
    /*
    .team-grid {
      display: grid;
      grid-auto-flow: column;
      grid-auto-columns: minmax(220px, 1fr);
      overflow-x: auto;
      gap: 16px;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 8px;
    }
    */
  </style>
</head>

<body>
  <!-- Header -->
  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>

  <div class="page-wrapper">
    <div class="content clearfix"></div>

    <div class="main-content-wrapper">
      <div style="text-align:center;">
        <h1 class="post-title">Unser Team</h1>
      </div>

      <div class="team-wrapper">
        <div class="team-grid">
          <!-- Emily -->
          <article class="team-card">
            <img src="<?php echo BASE_URL . '/assets/images/Profilbild.png'; ?>" alt="Profilbild Emily">
            <div class="container">
              <h2>Emily</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <!-- Nisa -->
          <article class="team-card">
            <img src="<?php echo BASE_URL . '/assets/images/Profilbild.png'; ?>" alt="Profilbild Nisa">
            <div class="container">
              <h2>Nisa</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <!-- Antonia -->
          <article class="team-card">
            <img src="<?php echo BASE_URL . '/assets/images/Profilbild.png'; ?>" alt="Profilbild Antonia">
            <div class="container">
              <h2>Antonia</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <!-- Lavina -->
          <article class="team-card">
            <img src="<?php echo BASE_URL . '/assets/images/Profilbild.png'; ?>" alt="Profilbild Lavina">
            <div class="container">
              <h2>Lavina</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>
        </div>
      </div>
    </div><!-- /main-content-wrapper -->
  </div><!-- /page-wrapper -->

  <!-- Footer -->
  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="assets/js/scripts.js"></script>
</body>
</html>
