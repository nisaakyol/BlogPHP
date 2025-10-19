<!-- Veränderungsdatum: 08.10.2024 
      DIe Team Seite besteht nur aus Hardcode mit dem entsprechenden CSS Stil für die Bilder und den zugehörigen Text, um die "Entwickler" abzubilden.  
-->

<?php require("../path.php") ?>
<?php require(ROOT_PATH . "/app/controllers/posts.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

    <!-- Custom Styling -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <title>Unser Team</title>
</head>


<!-- Navigationsleiste für Blog Eintrag aus header-->
<?php include(ROOT_PATH . "/app/includes/header.php"); ?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <div class="content clearfix"></div>

    <div class="main-content-wrapper">

        <div style="text-align: center;">
            <h1 class="post-title">Unser Team</h1>
        </div>

        <!-- Verschiedene Colums für Bild und Bild Unterschrift -->
        <div class="row">
            <div class="column">
                <div class="card">
                    <img src="<?php echo BASE_URL . '/assets/images/Kenan1.png' ?>" alt="Jane" style="width:100%">
                    <div class="container">
                        <h2>Kenan Pehlivan</h2>
                        <p class="title">Developer</p>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="card">
                    <img src="<?php echo BASE_URL . '/assets/images/Alex3.png' ?>" alt="Mike" style="width:100%">
                    <div class="container">
                        <h2>Alexander Beine</h2>
                        <p class="title">Art Director</p>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="card">
                    <img src="<?php echo BASE_URL . '/assets/images/Sven2.png' ?>" alt="John" style="width:100%">
                    <div class="container">
                        <h2>Sven Fritzler</h2>
                        <p class="title">Designer</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="column2">
                <div class="card">
                    <img src="<?php echo BASE_URL . '/assets/images/Markus2.png' ?>" alt="John" style="width:100%">

                    <div class="container">
                        <h2>Markus Duong</h2>
                        <p class="title">Data Engineer</p>
                    </div>
                </div>
            </div>

            <div class="column2">
                <div class="card">
                    <img src="<?php echo BASE_URL . '/assets/images/Chan.png' ?>" alt="John" style="width:100%">

                    <div class="container">
                        <h2>Chanpreet Singh</h2>
                        <p class="title">Developer</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>


<!-- Fußzeile aus includes footer mit GLobaler Variable um Sprung Fehler zu vermeiden -->
<?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

<!-- JQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Slick Carousel -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<!-- JS Skript -->
<script src="assets/js/scripts.js"></script>



</html>