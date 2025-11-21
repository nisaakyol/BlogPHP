<?php
// Pfad-/URL-Konstanten laden
require __DIR__ . '/../../path.php';
require ROOT_PATH . '/app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />

  <title>Unser Team</title>

  <style>
  /* Sand-Hintergrund wie auf Start & About */
  html,
  body,
  .page-wrapper {
    background: #efe7dd !important;
    margin: 0;
    padding: 0;
  }

  .page-wrapper {
    padding-top: 10px;
  }

  /* Titel */
  .team-title {
  text-align: center;
  font-size: 38px;
  font-weight: 700;
  margin-bottom: 45px;
  margin-top: 10px;        /* Neu: Überschrift hochziehen */
  color: #2e3a46;
  letter-spacing: 0.6px;
  font-family: "Poppins", sans-serif;
}

  /* Grid */
  .team-grid {
    width: 90%;
    max-width: 1100px;
    margin: 0 auto 80px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 28px;
  }

  /* Cards */
  .team-card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 12px 30px rgba(0,0,0,.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .25s ease;
  }

  .team-card:hover {
    transform: translateY(-4px);
  }

  .team-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
  }

  .team-card .container {
    padding: 20px 18px;
    text-align: center;
  }

  .team-card h2 {
    margin: 6px 0 4px;
    font-size: 20px;
    font-weight: 600;
    color: #2e3a46;
  }

  .team-card .title {
    color: #6f6f6f;
    font-size: 15px;
  }

  @media (max-width: 700px) {
    .team-title {
      font-size: 28px;
    }
    .team-grid {
      gap: 20px;
    }
  }
</style>
</head>

<body>
  <!-- Öffentlicher Seiten-Header mit Navigation und Logo -->
  <?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

  <!-- Gesamtseite: enthält den Hauptinhalt der Team-Seite -->
  <div class="page-wrapper">
    <div class="content clearfix"></div>

    <!-- Hauptinhalt der Seite (Titel und Teamübersicht) -->
    <div class="main-content-wrapper">
      <div style="text-align:center;">
        <h1 class="post-title">Unser Team</h1>
      </div>

      <!-- Team-Bereich: Grid mit allen Teammitgliedern -->
      <div class="team-wrapper">
        <!-- Raster mit allen Teamkarten (Foto + Name + Rolle) -->
        <div class="team-grid">
          <article class="team-card">
            <img src="<?php echo BASE_URL . '/public/resources/assets/images/Profilbild.png'; ?>" alt="Profilbild Emily">
            <div class="container">
              <h2>Emily</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <article class="team-card">
            <img src="<?php echo BASE_URL . '/public/resources/assets/images/NisaProfilbild.jpeg'; ?>" alt="Profilbild Nisa">
            <div class="container">
              <h2>Nisa</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <article class="team-card">
            <img src="<?php echo BASE_URL . '/public/resources/assets/images/Profilbild.png'; ?>" alt="Profilbild Antonia">
            <div class="container">
              <h2>Antonia</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>

          <article class="team-card">
            <img src="<?php echo BASE_URL . '/public/resources/assets/images/Profilbild.png'; ?>" alt="Profilbild Lavina">
            <div class="container">
              <h2>Lavinia</h2>
              <p class="title">Teammitglied</p>
            </div>
          </article>
        </div>
      </div>
    </div><!-- /main-content-wrapper -->
  </div><!-- /page-wrapper -->

  <?php include(ROOT_PATH . "/app/support/includes/footer.php"); ?>

  <!-- JavaScript für Sliders, UI-Effekte und allgemeine Seitenskripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="/public/resources/assets/js/scripts.js"></script>
</body>
</html>
