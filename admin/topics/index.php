<?php
/**
 * Datei: admin/topics/index.php
 * Zweck: Übersicht/Verwaltung der Topics (Listenansicht, Edit/Delete)
 *
 * Hinweise:
 * - Delete wird derzeit per GET-Parameter ausgelöst (index.php?del_id=...).
 *   Für Produktion empfehlenswert: POST + CSRF-Token.
 * - $ctrl->index() liefert die Topics-Liste.
 */

require __DIR__ . '/../_admin_boot.php';

use App\OOP\Controllers\Admin\AdminTopicController;
use App\OOP\Repositories\DbRepository;

// Controller instanzieren
$ctrl = new AdminTopicController(new DbRepository());

// Lösch-Aktion (aktuell GET-basiert; später besser POST + CSRF)
if (isset($_GET['del_id'])) {
  $ctrl->destroy((int) $_GET['del_id']);
}

// Daten für die Tabelle laden
$topics = $ctrl->index() ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- Font Awesome (Icons) -->
  <link
    rel="stylesheet"
    href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr"
    crossorigin="anonymous"
  />

  <!-- Google Fonts (legacy) -->
  <link href="https://fonts.googleapis.com/css?family=Candal|Lora" rel="stylesheet" />

  <!-- Basis-Styles -->
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <!-- Admin-Styles -->
  <link rel="stylesheet" href="../../assets/css/admin.css" />

  <title>Admin Section - Manage Topics</title>
</head>
<body>
  <!-- Oberer Admin-Header -->
  <?php include ROOT_PATH . "/app/includes/adminHeader.php"; ?>

  <!-- Seiten-Wrapper -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar -->
    <?php include ROOT_PATH . "/app/includes/adminSidebar.php"; ?>

    <!-- Hauptinhalt -->
    <div class="admin-content">
      <!-- Schnellzugriff -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Topic</a>
        <a href="index.php"  class="btn btn-big">Manage Topics</a>
      </div>

      <div class="content">
        <h2 class="page-title">Manage Topics</h2>

        <!-- System-/Flash-Meldungen -->
        <?php include ROOT_PATH . "/app/includes/messages.php"; ?>

        <!-- Tabelle der Topics -->
        <table>
          <thead>
            <tr>
              <th>SN</th>
              <th>Name</th>
              <th colspan="2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topics as $idx => $topic): ?>
              <?php
                $topicId   = (int) ($topic['id']   ?? 0);
                $topicName = (string) ($topic['name'] ?? '');
              ?>
              <tr>
                <!-- Laufnummer (1-basiert) -->
                <td><?php echo $idx + 1; ?></td>

                <!-- Topic-Name -->
                <td><?php echo htmlspecialchars($topicName, ENT_QUOTES, 'UTF-8'); ?></td>

                <!-- Aktionen: Edit/Delete (Delete aktuell GET-basiert) -->
                <td>
                <a class="btn btn--sm btn--success"
                    href="edit.php?id=<?php echo (int)$topic['id']; ?>">
                    Edit
                </a>
                <a class="btn btn--sm btn--danger"
                    href="index.php?del_id=<?php echo (int)$topic['id']; ?>"
                    onclick="return confirm('Topic wirklich löschen?');">
                    Delete
                </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- /.content -->
    </div><!-- /.admin-content -->
  </div><!-- /.admin-wrapper -->

  <!-- Vendor-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- CKEditor (falls in diesem Screen benötigt) -->
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <!-- Projekt-JS -->
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
