<?php
require __DIR__ . '/../_admin_boot.php'; usersOnly();
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

$ctrl = new AdminPostController(new DbRepository());

// Actions wie früher
if (isset($_GET['delete_id']))             $ctrl->delete((int)$_GET['delete_id']);
if (isset($_GET['published'], $_GET['p_id'])) $ctrl->togglePublish((int)$_GET['p_id'], (int)$_GET['published']);

$vm = $ctrl->index();
$posts = $vm['posts'];
$usersById = $vm['usersById'];
?>
<!-- Rest deines HTML bleibt – aber wir ersetzen displayPosts.php durch TOP-Version -->


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
    <link rel="stylesheet" href="../../assets/css/style.css">

    <!-- Admin CSS Stil -->
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <title>Admin Section - Manage Posts</title>
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
            <div class="button-group">
                <a href="create.php" class="btn btn-big">Add Post</a>
                <a href="index.php" class="btn btn-big">Manage Posts</a>
            </div>

            <div class="content">
                <h2 class="page-title">Manage Posts</h2>

                <?php include(ROOT_PATH . "/app/includes/messages.php"); ?>
                <table>
                    <thead>
                        <th>SN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th colspan="3">Action</th>
                    </thead>
                    <tbody>
                        <!-- Zeige alle Posts an -->
                        <?php require(ROOT_PATH . "/admin/posts/displayPosts.php"); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Ckeditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
    <!-- JS Skript -->
    <script src="../../assets/js/scripts.js"></script>

</body>
</html>
