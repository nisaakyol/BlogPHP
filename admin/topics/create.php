<?php
require __DIR__ . '/../_admin_boot.php'; adminOnly();
require_once ROOT_PATH . '/app/OOP/bootstrap.php';

use App\OOP\Controllers\Admin\AdminTopicController;
use App\OOP\Repositories\DbRepository;

$ctrl = new AdminTopicController(new DbRepository());
$vm = $ctrl->create($_POST);

$errors = $vm['errors'] ?? [];
$name   = $vm['name'] ?? '';
$description = $vm['description'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/admin.css">
  <title>Admin Section - Add Topic</title>
</head>
<body>
<?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>
<div class="admin-wrapper">
  <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>
  <div class="admin-content">
    <div class="button-group">
      <a href="create.php" class="btn btn-big">Add Topic</a>
      <a href="index.php" class="btn btn-big">Manage Topics</a>
    </div>

    <div class="content">
      <h2 class="page-title">Add Topic</h2>
      <?php include ROOT_PATH . "/app/helpers/formErrors.php"; ?>

      <form action="create.php" method="post">
        <div>
          <label>Name</label>
          <input type="text" name="name" value="<?php echo $name; ?>" class="text-input">
        </div>
        <div>
          <label>Description</label>
          <textarea name="description" id="body"><?php echo $description; ?></textarea>
        </div>
        <div><button type="submit" name="add-topic" class="btn btn-big">Add Topic</button></div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
<script src="../../assets/js/scripts.js"></script>
</body>
</html>
