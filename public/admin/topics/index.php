<?php
require __DIR__ . '/../_admin_boot.php';
adminOnly(); // nur Admins

require_once ROOT_PATH . '/app/bootstrap.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DBRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/Admin/AdminTopicController.php';

use App\Http\Controllers\Admin\AdminTopicController;
use App\Infrastructure\Repositories\DbRepository;

$ctrl = new AdminTopicController(new DbRepository()); // Controller

// Delete via GET (bestehendes Verhalten)
if (isset($_GET['del_id'])) {
  $ctrl->destroy((int) $_GET['del_id']);
  header('Location: ' . BASE_URL . '/admin/topics/index.php');
  exit;
}

// ViewModel laden
$vm     = $ctrl->index();
$topics = $vm['topics'] ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section - Manage Topics</title>
</head>
<body>
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">
          <i class="fas fa-plus"></i> Add Topic
        </a>
        <a href="index.php" class="btn btn--lg btn--ghost">
          <i class="fas fa-list"></i> Manage Topics
        </a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Topics</h2>

        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

        <table>
          <thead>
            <tr>
              <th>SN</th>
              <th>Name</th>
              <th colspan="2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($topics)): ?>
              <tr>
                <td colspan="3">Keine Topics vorhanden.</td>
              </tr>
            <?php else: ?>
              <?php $sn = 1; ?>
              <?php foreach ($topics as $topic): ?>
                <?php
                  $topicId   = (int)($topic['id'] ?? 0);
                  $topicName = (string)($topic['name'] ?? '');
                ?>
                <tr>
                  <td><?= $sn++ ?></td>
                  <td><?= htmlspecialchars($topicName, ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="table-actions">
                    <a href="edit.php?id=<?= $topicId ?>" class="btn btn--sm btn--success">
                      <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="index.php?del_id=<?= $topicId ?>"
                       class="btn btn--sm btn--danger"
                       onclick="return confirm('Topic wirklich löschen?');">
                      <i class="fas fa-trash"></i> Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="../resources/assets/js/scripts.js"></script>
</body>
</html>
