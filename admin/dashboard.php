<?php
require __DIR__ . '/_admin_boot.php'; adminOnly();
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminDashboardController;
use App\OOP\Repositories\DbRepository;

$vm = (new AdminDashboardController(new DbRepository()))->stats();
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

    <!-- Admin CSS Stil -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <title>Admin Section - Dashboard</title>

    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL . '/assets/images/favicon-32x32.png' ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL . '/assets/images/favicon-16x16.png' ?>">
    <link rel="manifest" href="<?php echo BASE_URL . '/assets/images/site.webmanifest' ?>">
</head>

<body>

    <!-- Einfügen des Admin headers aus includes -->
    <?php include(ROOT_PATH . "/app/includes/adminHeader.php"); ?>

    <!-- Admin Page Wrapper -->
    <div class="admin-wrapper">

        <!-- Linke Sidebar aus include adminSidebar mit verschieden Verwaltungsoptionen -->
        <?php include(ROOT_PATH . "/app/includes/adminSidebar.php"); ?>

        <!-- Admin Content -->
        <div class="admin-content">
            <div class="content">
                <h2 class="page-title">Dashboard</h2>
                <!-- Zeige Success/Error Messages -->
                <?php include(ROOT_PATH . '/app/includes/messages.php'); ?>
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
