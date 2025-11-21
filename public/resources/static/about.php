<?php
declare(strict_types=1);

require __DIR__ . '/../../path.php';
require ROOT_PATH . '/app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        crossorigin="anonymous" />

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,600|Lora" rel="stylesheet" />

  <link rel="stylesheet" href="../assets/css/style.css" />

    <style>
    /* gleicher Sand-Hintergrund wie auf der Startseite */
    html,
    body,
    .page-wrapper {
      background: #efe7dd !important;
      margin: 0;
      padding: 0;
    }

    .page-wrapper {
      padding-top: 40px; /* Abstand unter dem Header */
    }

    /* Hero-Bereich oben */
    .about-hero {
      width: 90%;
      margin: 0 auto 35px;
      padding: 32px 40px;

      background: #2e3a46;
      color: #efe7dd;
      border-radius: 22px;
      box-shadow: 0 18px 45px rgba(0,0,0,.18);
      text-align: left;
    }

    .about-hero h1 {
      margin: 0 0 10px;
      font-size: 34px;
      letter-spacing: .4px;
      color: #ffffff;
    }

    .about-hero p {
      margin: 0;
      font-size: 16px;
      opacity: .92;
    }

    /* Karten-Bereich */
    .about-content {
      width: 90%;
      margin: 0 auto 80px;
    }

    .about-card {
      background: #ffffff;
      padding: 32px 40px;
      border-radius: 22px;
      box-shadow: 0 12px 30px rgba(0,0,0,.10);
    }

    .about-card h2 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 24px;
      color: #2e3a46;
    }

    .about-card p,
    .about-card ul {
      font-size: 16px;
      line-height: 1.7;
      color: #222;
    }

    .about-card ul {
      margin: 12px 0 22px 24px;
    }

    @media (max-width: 900px) {
      .about-hero,
      .about-content,
      .about-card {
        width: 100%;
        padding-left: 22px;
        padding-right: 22px;
      }
    }
  </style>

  <title>About Us</title>
</head>

<body>

<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

<div class="page-wrapper">

  <!-- Hero oben -->
  <section class="about-hero">
    <h1>Travel-Blog für dein Auslandssemester</h1>
    <p>
      Ehrliche Einblicke, Erfahrungsberichte und Tipps rund um das Leben und Studieren im Ausland – 
      von Studierenden für Studierende.
    </p>
  </section>

  <!-- Inhalt als Karte -->
  <section class="about-content">
    <div class="about-card">
      <h2>Worum geht es hier?</h2>

      <p>
        Auf diesem Blog findet ihr viele Eindrücke, Erfahrungen und Informationen rund um das Thema Auslandssemester.
        Im Mittelpunkt stehen fünf Länder, die ganz unterschiedliche Seiten der Welt zeigen:
      </p>

      <ul>
        <li>Singapur</li>
        <li>Neuseeland</li>
        <li>Australien</li>
        <li>Irland</li>
        <li>Südafrika</li>
      </ul>

      <p>
        Jedes dieser Länder hat seinen eigenen Charakter, eine besondere Atmosphäre und ganz eigene Möglichkeiten,
        das Leben und Studieren einmal anders kennenzulernen.
      </p>

      <p>
        Hier könnt ihr nachlesen, wie das Leben in diesen Ländern aussieht, wie das Studium dort abläuft und was
        den Alltag als internationale Studierende prägt. Zu jedem Land gibt es mehrere Artikel, die euch einen guten
        Überblick geben – über Campusalltag, Reiseziele und praktische Tipps.
      </p>

      <p>
        Dieser Blog richtet sich an alle, die neugierig auf die Welt sind und Lust haben, Neues zu entdecken.
        Vielleicht plant ihr selbst ein Auslandssemester oder wollt einfach erfahren, wie das Leben in anderen
        Ländern wirklich aussieht. Hier findet ihr ehrliche Eindrücke, hilfreiche Informationen und kleine Geschichten.
      </p>
    </div>
  </section>

</div>

<?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="../assets/js/scripts.js"></script>

</body>
</html>
