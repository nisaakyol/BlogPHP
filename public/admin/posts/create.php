<?php
// Zweck: Admin – Formular zum Erstellen eines neuen Blog-Posts (Create-Ansicht)

declare(strict_types=1);

require __DIR__ . '/../_admin_boot.php'; // Session/ROOT_PATH/BASE_URL/Guards
usersOnly(); // nur eingeloggte Benutzer

require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminPostController.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';

use App\Http\Controllers\Admin\AdminPostController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminPostController(new DbRepository());
$vm   = $ctrl->handleCreate($_POST, $_FILES);

// ViewModel entpacken (Defaults)
$errors    = $vm['errors']    ?? [];
$title     = $vm['title']     ?? '';
$body      = $vm['body']      ?? '';
$topic_id  = $vm['topic_id']  ?? '';
$published = $vm['published'] ?? '';
$topics    = $vm['topics']    ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section – Add Post</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php" class="btn btn-big">Manage Posts</a>
      </div>

      <div class="content">
        <h2 class="page-title">Create Posts</h2>

        <?php include ROOT_PATH . "/app/Support/helpers/formErrors.php"; ?>

        <form action="create.php" method="post" enctype="multipart/form-data">
          <div>
            <label for="title">Title</label>
            <input
              type="text"
              id="title"
              name="title"
              class="text-input"
              value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>

          <div>
            <label for="body">Body</label>
            <textarea
              name="body"
              id="body"
            ><?php echo htmlspecialchars($body, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div>
            <label for="image">Image</label>
            <input type="file" id="image" name="image" class="text-input">
          </div>

          <div>
            <label for="topic_id">Topic</label>
            <select id="topic_id" name="topic_id" class="text-input">
              <option value=""></option>
              <?php foreach ($topics as $topic): ?>
                <option
                  value="<?php echo (int)$topic['id']; ?>"
                  <?php echo (!empty($topic_id) && (int)$topic_id === (int)$topic['id']) ? 'selected' : ''; ?>
                >
                  <?php echo htmlspecialchars($topic['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <?php if (!empty($_SESSION['admin'])): ?>
              <label>
                <input
                  type="checkbox"
                  name="published"
                  <?php echo !empty($published) ? 'checked' : ''; ?>
                >
                Publish
              </label>
            <?php else: ?>
              <label>
                <input
                  type="checkbox"
                  name="AdminPublish"
                  <?php echo !empty($published) ? 'checked' : ''; ?>
                >
                Zum Publishen an Admin senden
              </label>
            <?php endif; ?>
          </div>

          <div>
            <button type="submit" name="add-post" class="btn btn-big">Add Post</button>
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
