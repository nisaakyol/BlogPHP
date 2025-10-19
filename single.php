<?php
require 'path.php';
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\PostReadController;
use App\OOP\Repositories\DbRepository;

$id = (int)($_GET['id'] ?? 0);
$ctrl = new PostReadController(new DbRepository());
$vm = $ctrl->show($id);

$post = $vm['post'];
$comments = $vm['comments'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $post['title']; ?> | DHBW-BLOG</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

    <!-- CSS Stil -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .content {

            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
    </style>

</head>

<body>

    <!-- Header -->
    <?php include(ROOT_PATH . "/app/includes/header.php"); ?>

    <!-- Page Wrapper -->
    <div class="page-wrapper">

        <!-- Content -->
        <div class="content clearfix">

            <!-- Main Content Wrapper -->
            <div class="main-content-wrapper">
                <!-- Post Section -->
                <section class="post-section">
                    <article class="post">
                        <!-- Post Title -->
                        <header class="post-header">
                            <h1 class="post-title"><?php echo $post['title']; ?></h1>
                        </header>
                        <!-- Post Content -->
                        <div class="post-content">
                            <?php echo html_entity_decode($post['body']); ?>
                        </div>
                    </article>
                </section>

                <!-- Comment Section -->
                <section class="comment-section">
                    <!-- Display Comments -->
                    <?php display_comments($post['id']); ?>

                    <h3 class="comment-title">Kommentar hinzufügen</h3>
                    <form id="comment-form" action="single.php" method="post">
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="post_id" id="post_id" value="<?php echo $post['id']; ?>">

                        <!-- Username Field -->
                        <div class="form-group">
                            <label for="username">Benutzername:</label><br>
                            <input type="text" id="username" name="username" required class="form-input">
                        </div>

                        <!-- Comment Field -->
                        <div class="form-group">
                            <label for="comment">Kommentar:</label><br>
                            <textarea id="comment" name="comment" rows="4" cols="50" required
                                class="form-textarea"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <input type="submit" value="Senden" class="btn-submit">
                        </div>
                    </form>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="sidebar single">

                <!-- Alle Posts mit Bild im CSS Stil auf der single.php Seite aufrufen und in der Sidebar anzeigen -->
                <div class="section popular">
                    <h2 class="section-title">Popular</h2>
                    <?php foreach ($posts as $p): ?>
                        <div class="post clearfix">
                            <img src="<?php echo BASE_URL . '/assets/images/' . $p['image']; ?>" alt="">
                            <a href="<?php echo BASE_URL . '/single.php?id=' . $p['id']; ?>" class="title">
                                <h4><?php echo $p['title']; ?></h4>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Topics Section -->
                <div class="section topics">
                    <h2 class="section-title">Topics</h2>
                    <ul>
                        <?php foreach ($topics as $topic): ?>
                            <li>
                                <a
                                    href="<?php echo BASE_URL . '/index.php?t_id=' . $topic['id'] . '&name=' . $topic['name']; ?>">
                                    <?php echo $topic['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include(ROOT_PATH . "/app/includes/footer.php"); ?>

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Slick Carousel -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>


    <!-- JS SKript -->
    <script src="assets/js/scripts.js"></script>
</body>

</html>