<?php
declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php'; // Session/ROOT_PATH/BASE_URL
adminOnly(); // nur Admins

require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminTopicController.php';

use App\Http\Controllers\Admin\AdminTopicController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminTopicController(new DbRepository()); // Controller

// POST → speichern (Controller handled Redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->store($_POST);
    exit;
}

// GET → Formular anzeigen
$vm     = $ctrl->create();
$topic  = $vm['topic']  ?? ['name' => '', 'description' => ''];
$errors = $_SESSION['errors'] ?? $vm['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']); // einmalig anzeigen
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section – Add Topic</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="index.php" class="btn btn-big btn--ghost">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Add Topic</h2>

        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

        <?php if (!empty($errors)): ?>
          <div class="msg error">
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="create.php" method="post">
          <div class="input-group">
            <label for="name">Name *</label>
            <input
              type="text"
              id="name"
              name="name"
              class="text-input"
              required
              value="<?= htmlspecialchars($old['name'] ?? $topic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
          </div>

          <div class="input-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              name="description"
              rows="5"
              class="text-input"
            ><?= htmlspecialchars($old['description'] ?? $topic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="input-group">
            <button type="submit" class="btn btn-big btn--primary">Save</button>
          </div>
        </form>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script> -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
