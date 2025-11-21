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
  <style>
/* Gesamter Hintergrund wie Admin */
body {
  background: #efe7dd !important;
  font-family: 'Poppins', sans-serif;
}

/* Weißer Card-Container */
.admin-content .content {
  background: #ffffff;
  padding: 28px 32px;
  border-radius: 22px;
  max-width: 900px;
  margin: 0 auto;
  box-shadow: 0 18px 55px rgba(0,0,0,.08);
  border: 1px solid rgba(0,0,0,.03);
}

/* Titel */
.page-title {
  text-align: center;
  font-size: 1.7rem;
  font-weight: 700;
  margin-bottom: 1.4rem;
  color: #2e3a46;
}

/* Buttons oben */
.button-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin: 20px 0 18px;
  align-items: flex-start;
}

.btn.btn-big {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 0.55rem 1.3rem;
  border-radius: 999px;
  background: rgb(198, 194, 198) !important;
  border: 1px solid #080808 !important;
  color: white !important;
  font-size: .95rem;
}

.btn.btn-big:hover {
  background: #030410 !important;
}

/* Inputs */
.text-input,
textarea {
  border-radius: 12px !important;
  border: 1px solid #dcdcdc !important;
  padding: 0.7rem 1rem !important;
  font-size: 1rem !important;
  width: 100%;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
  display: block;
}
</style>
</head>
<body>
  <!-- Globaler Admin-Header (Navigation, Benutzerinformationen) -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <!-- Haupt-Container für das Admin-Layout -->
  <div class="admin-wrapper">
    <!-- Linke Seitenleiste mit Admin-Menüs -->
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <div class="admin-content">
      <div class="button-group">
        <a href="index.php" class="btn btn-big btn--ghost">Manage Topics</a>
      </div>

     <!-- Hauptbereich zum Erstellen oder Bearbeiten eines Topics -->
      <div class="content">
        <!-- Überschrift der Topic-Management-Seite -->
        <h2 class="page-title">Add Topic</h2>

        <!-- Systemmeldungen anzeigen (Erfolg, Warnungen, Hinweise) -->
        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

        <!-- Validierungsfehler für das Topic-Formular anzeigen -->
        <?php if (!empty($errors)): ?>
          <div class="msg error">
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Formular zum Erstellen eines neuen Topics -->
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
            <!-- Optionale Beschreibung des Topics -->
            <label for="description">Description</label>
            <textarea
              id="description"
              name="description"
              rows="5"
              class="text-input"
            ><?= htmlspecialchars($old['description'] ?? $topic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div class="input-group">
            <!-- Topic speichern -->
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
