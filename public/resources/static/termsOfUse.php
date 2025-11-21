<?php 
// Pfade/Bootstrap laden
require dirname(__DIR__, 2) . '/path.php';  
require ROOT_PATH . '/app/bootstrap.php'; 
?> 
<!DOCTYPE html>
<html lang="en">

  <style>
    /* Sand-Hintergrund wie auf Start/About */
    html,
    body,
    .page-wrapper {
      background: #efe7dd !important;
      margin: 0;
      padding: 0;
    }

    .page-wrapper {
      padding-top: 1px; /* Abstand unter dem Header */
    }

    /* Hero-Banner oben */
    .terms-hero {
      width: 90%;
      margin: 0 auto 35px;
      padding: 28px 40px;

      background: #2e3a46;
      color: #efe7dd;
      border-radius: 22px;
      box-shadow: 0 18px 45px rgba(0,0,0,.18);
      text-align: left;
    }

    .terms-hero h1 {
      margin: 0 0 8px;
      font-size: 32px;
      font-weight: 600;
      letter-spacing: .4px;
      color: #ffffff;
    }

    .terms-hero p {
      margin: 0;
      font-size: 16px;
      opacity: .92;
    }

    /* Karte für den Text */
    .terms-wrapper {
      width: 90%;
      margin: 0 auto 80px;
    }

    .terms-card {
      background: #ffffff;
      padding: 32px 40px;
      border-radius: 22px;
      box-shadow: 0 12px 30px rgba(0,0,0,.10);
      font-size: 16px;
      line-height: 1.7;
      color: #222;
    }

    .terms-card p {
      margin: 0 0 14px;
    }

    .terms-card a {
      color: #2e3a46;
      text-decoration: underline;
    }

    .terms-card strong {
      font-weight: 600;
    }

    @media (max-width: 900px) {
      .terms-hero,
      .terms-wrapper,
      .terms-card {
        width: 100%;
        padding-left: 22px;
        padding-right: 22px;
      }
    }
  </style>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/style.css">

  <title>Terms and Conditions</title>
</head>

<?php include(ROOT_PATH . "/app/Support/includes/header.php"); ?>

<div class="page-wrapper">

  <div class="content clearfix"></div>

  <!-- Hero-Banner -->
  <section class="terms-hero">
    <h1>Nutzungsbedingungen</h1>
    <p>
      Hier findest du die wichtigsten Regeln zur Nutzung des Travel-Blogs – bitte lies sie dir
      aufmerksam durch, bevor du die Inhalte verwendest.
    </p>
  </section>

  <!-- Inhalt als Karte -->
  <div class="terms-wrapper">
    <div class="terms-card">

      <p><strong>1. Geltungsbereich</strong></p>
      <p>Diese Nutzungsbedingungen gelten für alle Besucherinnen und Besucher des Travel-Blogs <strong>[Blogname]</strong> 
         unter <strong>[www.blogname.de]</strong>. Durch den Zugriff auf diese Website erklärst du dich mit den folgenden 
         Bedingungen einverstanden.</p>

      <p><strong>2. Inhalt des Angebots</strong></p>
      <p>Der Blog bietet persönliche Reiseberichte, Fotos und Tipps rund um das Thema Reisen. 
         Die Inhalte dienen ausschließlich informatorischen und unterhaltenden Zwecken und stellen 
         keine Reiseberatung oder Buchungsplattform dar.</p>

      <p><strong>3. Urheberrecht</strong></p>
      <p>Alle Texte, Fotos und Videos sind – sofern nicht anders gekennzeichnet – Eigentum von 
         <strong>[Blogname]</strong> oder der jeweiligen Urheberinnen und Urheber. 
         Eine Vervielfältigung oder Weiterverwendung außerhalb der Grenzen des Urheberrechts 
         ist nur mit schriftlicher Zustimmung erlaubt.</p>

      <p><strong>4. Kommentare</strong></p>
      <p>Kommentare sind willkommen, solange sie sachlich bleiben und keine 
         rechtswidrigen, diskriminierenden oder werblichen Inhalte enthalten. 
         Der Betreiber behält sich vor, Kommentare zu löschen oder zu bearbeiten.</p>

      <p><strong>5. Haftungsausschluss</strong></p>
      <p>Alle Informationen werden mit größter Sorgfalt erstellt, jedoch ohne Gewähr 
         auf Richtigkeit, Vollständigkeit oder Aktualität. 
         Der Betreiber übernimmt keine Haftung für Schäden, die durch die Nutzung 
         der Inhalte entstehen.</p>

      <p><strong>6. Werbung / Affiliate-Links</strong></p>
      <p>Der Blog kann Affiliate-Links oder bezahlte Kooperationen enthalten. 
         Diese werden gekennzeichnet. 
         Wenn du über einen solchen Link buchst oder kaufst, erhält <strong>[Blogname]</strong> 
         ggf. eine Provision, ohne dass dir Mehrkosten entstehen.</p>

      <p><strong>7. Datenschutz</strong></p>
      <p>Informationen zur Erhebung und Verarbeitung personenbezogener Daten findest du 
         in unserer <a href="datenschutz.php">Datenschutzerklärung</a>.</p>

      <p><strong>8. Änderungen</strong></p>
      <p>Der Betreiber behält sich vor, diese Nutzungsbedingungen jederzeit anzupassen. 
         Maßgeblich ist die jeweils aktuelle Version auf dieser Website.</p>

      <p><strong>9. Kontakt</strong></p>
      <p>Fragen zu diesen Nutzungsbedingungen bitte an 
         <a href="mailto:kontakt@blogname.de">kontakt@blogname.de</a>.</p>

      <p style="margin-top:30px;font-size:0.9em;color:#666;">Stand: 01.11.2025</p>

    </div>
  </div>

</div>

<?php include(ROOT_PATH . "/app/Support/includes/footer.php"); ?>

<!-- JavaScript für Sliders, UI-Effekte und allgemeine Seitenskripte -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="assets/js/scripts.js"></script>

</html>
