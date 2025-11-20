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
    body {
      background: #f3ede3 !important;
    }

    .about-wrapper {
      max-width: 900px;
      margin: 0 auto;            /* kein Abstand oben */
      padding-top: 40px;         /* leichter Atmer */
    }

    .about-card {
      background: #ffffff;
      padding: 40px 50px;
      border-radius: 26px;
      box-shadow: 0 18px 40px rgba(0,0,0,.15);
    }

    /* Titel-Balken */
    .about-title-bar {
      background: #2e3a46;           /* dein neues dunkles Travel-Blau */
      padding: 18px;
      border-radius: 18px;
      text-align: center;
      margin-bottom: 35px;
      color: #fff;
      font-size: 28px;
      font-weight: 600;
      letter-spacing: .5px;
    }

    /* Textgestaltung */
    .about-card p,
    .about-card ul {
      font-size: 17px;
      line-height: 1.6;
      color: #222;
    }

    .about-card ul {
      margin: 15px 0 25px 30px;
    }

  </style>

  <title>About Us</title>
</head>

<body>

<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

<div class="page-wrapper">

  <div class="about-wrapper">
    <div class="about-card">

      <div class="about-title-bar">
        Travel-Blog
      </div>

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
        den Alltag als internationale Studierende prägt. Zu jedem Land gibt es vier Artikel, die euch einen guten
        Überblick geben. Es geht um das Leben vor Ort, den Campusalltag, spannende Reiseziele und viele praktische Tipps.
      </p>

      <p>
        Dieser Blog richtet sich an alle, die neugierig auf die Welt sind und Lust haben, Neues zu entdecken.
        Vielleicht plant ihr selbst ein Auslandssemester oder wollt einfach erfahren, wie das Leben in anderen
        Ländern wirklich aussieht. Hier findet ihr ehrliche Eindrücke, hilfreiche Informationen und kleine Geschichten.
      </p>

    </div> <!-- about-card -->
  </div> <!-- about-wrapper -->

</div>

<?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="../assets/js/scripts.js"></script>

</body>
</html>
