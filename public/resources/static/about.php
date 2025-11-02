<?php
declare(strict_types=1);

require __DIR__ . '/../../path.php';           // ROOT_PATH & BASE_URL
require ROOT_PATH . '/app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <link rel="stylesheet" href="../assets/css/style.css" />

  <title>About Us</title>
</head>
<body>

  <?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

  <div class="page-wrapper">
    <div class="content clearfix"></div>

    <div class="main-content-wrapper">
      <div style="text-align: center;">
        <h1 class="post-title">Travel-Blog</h1>
      </div>

      <div style="margin-left: 35px; text-align: center;"></div>

      <div class="container">
        <div style="margin-left: 350px; margin-right: 320px; margin-top: 40px;">
          <section>
            <p>
            Auf diesem Blog findet ihr viele Eindrücke, Erfahrungen und Informationen rund um das Thema Auslandssemester. Im Mittelpunkt stehen fünf Länder, die ganz unterschiedliche Seiten der Welt zeigen: Singapur, Neuseeland, Australien, Irland und Südafrika. Jedes dieser Länder hat seinen eigenen Charakter, eine besondere Atmosphäre und ganz eigene Möglichkeiten, das Leben und Studieren einmal anders kennenzulernen.
            <br><br>Hier könnt ihr nachlesen, wie das Leben in diesen Ländern aussieht, wie das Studium dort abläuft und was den Alltag als internationale Studierende prägt. Zu jedem Land gibt es vier Artikel, die euch einen guten Überblick geben. Es geht um das Leben vor Ort, um den Campusalltag, um spannende Reiseziele und um viele praktische Tipps, die bei der Vorbereitung helfen können. So bekommt ihr einen echten Eindruck davon, wie unterschiedlich das Leben in diesen Ländern ist und was sie trotzdem miteinander verbindet.
            <br><br>Dieser Blog richtet sich an alle, die neugierig auf die Welt sind und Lust haben, Neues zu entdecken. Vielleicht plant ihr selbst ein Auslandssemester oder wollt einfach mehr darüber erfahren, wie das Leben in anderen Ländern wirklich aussieht. Hier findet ihr ehrliche Eindrücke, hilfreiche Informationen und kleine Geschichten, die zeigen, warum ein Studium im Ausland so viel mehr ist als nur eine Zeit an einer anderen Universität. Lasst euch inspirieren und entdeckt, welches Land am besten zu euch passt
            </p>
          </section>
        </div>
      </div>
      
  <?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script src="../assets/js/scripts.js"></script>
</body>
</html>
