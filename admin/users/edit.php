<!-- Veränderungsdatum 
    Diese Datei zeigt den Update-User Formular nachdem man von Manage User index Seite den edit anklickt.
    Mit diesen Formular kann ein user verändert bzw. akualisiert werden.
-->

<?php
require __DIR__ . '/../_admin_boot.php'; adminOnly();
require_once ROOT_PATH . "/app/controllers/users.php";
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <!-- Font Awesome -->
        <link rel="stylesheet"
            href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
            integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
            crossorigin="anonymous">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Candal|Lora"
            rel="stylesheet">

        <!-- CSS Styling -->
        <link rel="stylesheet" href="../../assets/css/style.css">

        <!-- Admin Styling -->
        <link rel="stylesheet" href="../../assets/css/admin.css">

        <title>Admin Section - Edit User</title>
    </head>

    <body>
       <!-- Einfügen des Admin headers aus includes verzeichnis -->
        <?php include(ROOT_PATH . "/app/includes/adminHeader.php"); ?>

        <!-- Admin Page Wrapper -->
        <div class="admin-wrapper">
            
             <!-- Linke Sidebar mit den Verwaltungsoptionen aus include adminSidebar -->
            <?php include(ROOT_PATH . "/app/includes/adminSidebar.php"); ?>

            <!-- Admin Content -->
            <div class="admin-content">
                <div class="button-group">
                    <a href="create.php" class="btn btn-big">Add User</a>
                    <a href="index.php" class="btn btn-big">Manage Users</a>
                </div>

                <div class="content">

                    <h2 class="page-title">Edit User</h2>

                    <!-- Alle Fehleingaben anzeigen -->
                    <?php include(ROOT_PATH . "/app/helpers/formErrors.php"); ?>

                    <!-- Mit dem drucken von Update-User button wird die Seite wieder geladen und alle variable mit POST "gespeichert"-->
                    <form action="edit.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $id; ?>" >    
                        <div>
                            <label>Username</label>
                            <input type="text" name="username" value="<?php echo $username; ?>" class="text-input">
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo $email; ?>" class="text-input">
                        </div>
                        <div>
                            <label>Password</label>
                            <input type="password" name="password" value="<?php echo $password; ?>" class="text-input">
                        </div>
                        <div>
                            <label>Password Confirmation</label>
                            <input type="password" name="passwordConf" value="<?php echo $passwordConf; ?>" class="text-input">
                        </div>
                        <div>
                            <!-- Wenn der Checkbox schon augewählt wurde, dann zeige es als ausgewählt -->
                            <?php if (isset($admin) && $admin == 1): ?>
                                <label>
                                    <input type="checkbox" name="admin" checked>
                                    Admin
                                </label>
                            <?php else: ?>
                                <label>
                                    <!-- Ansonsten zeige es als nicht ausgewählt -->
                                    <input type="checkbox" name="admin">
                                    Admin
                                </label>
                            <?php endif; ?>
                        </div>
                        <div>
                            <button type="submit" name="update-user" class="btn btn-big">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- JQuery -->
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <!-- Ckeditor -->
        <script
            src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
        <!-- JS Skript -->
        <script src="../../assets/js/scripts.js"></script>

    </body>

</html>
