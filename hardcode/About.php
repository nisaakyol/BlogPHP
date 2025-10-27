<?php
/**
 * Seite: hardcode/About.php
 * Hinweis:
 *  - Statischer Inhalt mit Verlinkungen auf offizielle DHBW-Seiten.
 *  - Struktur/Markup bereinigt (gültiges <html>, ein <body>, korrekte Schließ-Tags).
 */
require("../path.php"); // Initialisiert Pfad-/URL-Konstanten (z. B. ROOT_PATH, BASE_URL) für Includes/Assets.
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Externe Icon-Bibliothek (Font Awesome) für Symbole -->
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <!-- Google Webfonts: Typografie für Überschriften/Text -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <!-- Projektweites Stylesheet -->
  <link rel="stylesheet" href="../assets/css/style.css" />

  <title>About Us</title>
</head>
<body>

  <!-- Globale Navigationsleiste über PHP-Include; nutzt ROOT_PATH aus path.php -->
  <?php include(ROOT_PATH . "/app/includes/header.php"); ?>

  <!-- Seiten-Layout-Wrapper -->
  <div class="page-wrapper">
    <div class="content clearfix"></div> <!-- Layout-Hilfscontainer, hält Flows zusammen -->

    <div class="main-content-wrapper">
      <!-- Titelbereich (zentriert) -->
      <div style="text-align: center;">
        <h1 class="post-title">Profil der DHBW Stuttgart</h1>
      </div>

      <!-- Leer-/Abstandsblock (zentriert, mit linker Margin reserviert) -->
      <div style="margin-left: 35px; text-align: center;"></div>

      <!-- Einleitender Textabschnitt mit beschreibendem Inhalt -->
      <div class="container">
        <div style="margin-left: 350px; margin-right: 320px; margin-top: 40px;">
          <section>
            <p>
              text
            </p>
          </section>
        </div>
      </div>

      <!-- Highlight-/Erfahrungsbereich (Teaser) -->
      <div class="experience-section">
        <div class="experience-text">
          <h2>Überschrift</h2>
          <p>text</p>
        </div>
      </div>

      <!-- Buttonleiste mit externen Links zu offiziellen DHBW-Unterseiten -->
      <div class="button-container">
        <a href="https://www.deepl.com/de/translator" class="custom-button">Button</a>
      </div>  

  <!-- Globale Fußzeile via Include -->
  <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

  <!-- JS-Bibliotheken (jQuery, Slick Carousel) + projektweite Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> <!-- Basis-DOM/Utility -->
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script> <!-- Slider/Carousel -->
  <script src="../assets/js/scripts.js"></script> <!-- Initialisierung/Custom-Verhalten -->
</body>
</html>
