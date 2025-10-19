<?php
require __DIR__ . '/../_admin_boot.php'; usersOnly();
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminPostController;
use App\OOP\Repositories\DbRepository;

$ctrl = new AdminPostController(new DbRepository());
$vm = $ctrl->handleEdit($_GET, $_POST, $_FILES);

$errors   = $vm['errors'] ?? [];
$id       = $vm['id'] ?? '';
$title    = $vm['title'] ?? '';
$body     = $vm['body'] ?? '';
$topic_id = $vm['topic_id'] ?? '';
$published= $vm['published'] ?? '';
$topics   = $vm['topics'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <title>Admin Section - Edit Post</title>
</head>
<body>
<?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>
<div class="admin-wrapper">
  <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>
  <div class="admin-content">
    <div class="button-group">
      <a href="create.php" class="btn btn-big">Add Post</a>
      <a href="index.php" class="btn btn-big">Manage Posts</a>
    </div>

    <div class="content">
      <h2 class="page-title">Edit Posts</h2>
      <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

      <form action="edit.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div>
          <label>Title</label>
          <input type="text" name="title" value="<?php echo $title; ?>" class="text-input">
        </div>
        <div>
          <label>Body</label>
          <textarea name="body" id="body"><?php echo $body; ?></textarea>
        </div>
        <div>
          <label>Image</label>
          <input type="file" name="image" class="text-input">
        </div>
        <div>
          <label>Topic</label>
          <select name="topic_id" class="text-input">
            <option value=""></option>
            <?php foreach ($topics as $topic): ?>
              <option value="<?php echo $topic['id']; ?>"
                <?php echo (!empty($topic_id) && (int)$topic_id === (int)$topic['id']) ? 'selected' : ''; ?>>
                <?php echo $topic['name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <?php if (!empty($_SESSION['admin'])): ?>
            <label><input type="checkbox" name="published" <?php echo !empty($published) ? 'checked' : ''; ?>> Publish</label>
          <?php else: ?>
            <label><input type="checkbox" name="AdminPublish" <?php echo !empty($published) ? 'checked' : ''; ?>> Zum Publishen den Admin senden</label>
          <?php endif; ?>
        </div>
        <div><button type="submit" name="update-post" class="btn btn-big">Update Post</button></div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
<script src="../../assets/js/scripts.js"></script>
</body>
</html>
