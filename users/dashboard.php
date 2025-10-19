<!-- Veränderungsdatum: 08.10.2024 
      Die Dashboard Seite zeigt nach login alle Tätigkeiten die der Benutzer verwalten darf.
-->

<?php include("../path.php"); ?>
<?php include(ROOT_PATH . "/app/controllers/posts.php");
usersOnly();
?>
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

    <!-- CSS Stil -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Admin Stil -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <title>User Dashboard</title>
</head>

<body>
    <!-- Admin Header hier-->
    <?php include(ROOT_PATH . "/app/includes/adminHeader.php"); ?>

    <!-- Admin Page Wrapper -->
    <div class="admin-wrapper">

        <!-- Linke Sidebar mit Verwaltungsfunktionen -->
        <?php include(ROOT_PATH . "/app/includes/adminSidebar.php"); ?>

        <div class="admin-content">

            <div class="content">

                <h2 class="page-title">Dashboard</h2>

                <!-- Erfolgreich/ Nicht Erfolgreich Message -->
                <?php include(ROOT_PATH . "/app/includes/messages.php"); ?>
            </div>
        </div>
    </div>

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Ckeditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
    <!-- JS Skript -->
    <script src="../assets/js/scripts.js"></script>

</body>

</html>