<?php
declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php'; // ROOT_PATH/BASE_URL, Session, Guards
usersOnly(); // nur eingeloggte
adminOnly(); // nur Admins

require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminTopicController.php';

use App\Infrastructure\Repositories\DbRepository;
use App\Http\Controllers\Admin\AdminTopicController;

$ctrl = new AdminTopicController(new DbRepository()); // Controller

// UPDATE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-topic'])) {
    $ctrl->update((int)($_POST['id'] ?? 0), $_POST); // Update ausführen
    exit; // Update macht Redirect
}

// EDIT-Ansicht (GET)
$id = (int)($_GET['id'] ?? 0);
$vm = $ctrl->edit($id); // ViewModel laden

$topic  = $vm['topic']  ?? ['id'=>'','name'=>'','description'=>''];
$errors = $vm['errors'] ?? [];
$topics = $vm['topics'] ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Fonts & Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">

  <title>Admin – Edit Topic</title>
</head>
<body>

  <!-- Admin-Header & Sidebar -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Topic</a>
        <a href="index.php"  class="btn btn-big">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Edit Topic</h2>

        <!-- Fehlerausgabe -->
        <?php if (!empty($errors)): ?>
          <div class="msg error" role="alert">
            <ul style="margin:0 0 0 18px;">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars((string)$e, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Formular -->
        <form action="edit.php?id=<?= (int)$topic['id'] ?>" method="post">
          <input type="hidden" name="id" value="<?= (int)$topic['id'] ?>">

          <div>
            <label for="name">Name</label>
            <input
              type="text"
              id="name"
              name="name"
              class="text-input"
              value="<?= htmlspecialchars((string)($topic['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
              required
            >
          </div>

          <div>
            <label for="body">Description</label>
            <textarea
              name="description"
              id="body"
              rows="6"
            ><?= htmlspecialchars((string)($topic['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div>
            <button type="submit" name="update-topic" class="btn btn-big">Update Topic</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
