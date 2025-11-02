<?php
// Zweck: Admin – Ansicht zum Bearbeiten eines bestehenden Blog-Posts

declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php'; // Bootstrap (Session, Konstanten, Guards)
usersOnly(); // Zugriffsschutz

require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminPostController.php';

use App\Http\Controllers\Admin\AdminPostController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminPostController(new DbRepository());
$vm   = $ctrl->handleEdit($_GET, $_POST, $_FILES);

// ViewModel entpacken (mit Defaults)
$errors      = $vm['errors']      ?? [];
$id          = (int)($vm['id']    ?? 0);
$title       = (string)($vm['title'] ?? '');
$body        = (string)($vm['body']  ?? '');
$topic_id    = (int)($vm['topic_id'] ?? 0);
$published   = (int)($vm['published'] ?? 0);
$topics      = $vm['topics'] ?? [];
$currentImg  = (string)($vm['image'] ?? ''); // wichtig für Hidden-Feld & Vorschau

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section – Edit Post</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php"  class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Edit Posts</h2>

        <?php include ROOT_PATH . "/app/Support/helpers/formErrors.php"; ?>

        <form action="edit.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= (int)$id ?>">
          <input type="hidden" name="current_image" value="<?= $e($currentImg) ?>">

          <div>
            <label for="title">Title</label>
            <input
              type="text"
              id="title"
              name="title"
              class="text-input"
              value="<?= $e($title) ?>"
            >
          </div>

          <div>
            <label for="body">Body</label>
            <textarea
              name="body"
              id="body"
              class="text-input text-input--multiline"
            ><?= $e($body) ?></textarea>
          </div>

          <div>
            <label for="image">Image (leer lassen = altes Bild behalten)</label>
            <input type="file" id="image" name="image" class="text-input" accept="image/*">
            <?php if ($currentImg !== ''): ?>
              <div style="margin-top:10px">
                <strong>Aktuelles Bild:</strong><br>
                <img
                  src="<?= BASE_URL . '/public/resources/assets/images/' . $e($currentImg) ?>"
                  alt="aktuelles Bild"
                  style="max-width:380px;height:auto;border-radius:6px;"
                >
              </div>
            <?php endif; ?>
          </div>

          <div>
            <label for="topic_id">Topic</label>
            <select id="topic_id" name="topic_id" class="text-input">
              <option value=""></option>
              <?php foreach ($topics as $topic): ?>
                <?php
                  $optId   = (int)($topic['id'] ?? 0);
                  $optName = (string)($topic['name'] ?? '');
                ?>
                <option value="<?= $optId ?>" <?= ($topic_id === $optId ? 'selected' : '') ?>>
                  <?= $e($optName) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <?php if (!empty($_SESSION['admin'])): ?>
              <label>
                <input type="checkbox" name="published" <?= $published ? 'checked' : '' ?>>
                Publish
              </label>
            <?php else: ?>
              <label>
                <input type="checkbox" name="AdminPublish" <?= $published ? 'checked' : '' ?>>
                Zum Publishen an Admin senden
              </label>
            <?php endif; ?>
          </div>

          <div>
            <button type="submit" name="update-post" class="btn btn-big">Update Post</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
