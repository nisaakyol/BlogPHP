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
  header('Location: ' . BASE_URL . '/public/admin/topics/index.php');
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

  <style>
    /* -------- Grundlayout & Hintergrund (wie Manage Posts) -------- */
    body {
      margin: 0;
      background: #efe7dd; /* Sand */
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif;
    }

    .admin-wrapper {
      display: flex;
      min-height: calc(100vh - 66px); /* 66px = Header-Höhe */
      background: #efe7dd;
    }

    .admin-content {
      flex: 1;
      padding: 32px 40px 60px;
      box-sizing: border-box;
    }

    .admin-content .content {
      max-width: 1100px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 22px;
      box-shadow: 0 18px 42px rgba(0, 0, 0, 0.08);
      padding: 26px 36px 36px;
    }

    .page-title {
      margin: 0 0 18px;
      font-size: 1.8rem;
      text-align: center;
      color: #111827;
    }

    /* -------- Button-Gruppe oben -------- */
    .button-group {
      max-width: 1100px;
      margin: 0 auto 16px;
      display: flex;
      gap: 12px;
      justify-content: flex-start;
      align-items: center;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 999px;
      padding: 0.5rem 1.3rem;
      font-size: 0.95rem;
      border: none;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.05s ease;
      font-family: inherit;
    }

    .btn--lg {
      padding: 0.55rem 1.5rem;
      font-size: 0.98rem;
    }

    .btn--primary {
      background: #2e3a46;
      color: #ffffff;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
    }

    .btn--primary:hover {
      background: #1f2831;
    }

    .btn--ghost {
      background: #ffffff;
      color: #2e3a46;
      border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .btn--ghost:hover {
      background: #f3f4f6;
    }

    /* -------- Tabelle im Card-Stil -------- */
    .topics-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 0.96rem;
    }

    .topics-table thead {
      background: #f8f3ea;
    }

    .topics-table th,
    .topics-table td {
      padding: 10px 12px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }

    .topics-table th:first-child,
    .topics-table td:first-child {
      width: 60px;
    }

    .topics-table tbody tr:hover {
      background: #fafafa;
    }

    /* -------- Action-Chips (Edit/Delete) wie bei Posts -------- */
    .table-actions {
      display: flex;
      gap: 0.4rem;
      flex-wrap: wrap;
    }

    .btn-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 0.28rem 0.85rem;
      border-radius: 999px;
      font-size: 0.85rem;
      border: none;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.15s ease, transform 0.05s ease;
      font-family: inherit;
      white-space: nowrap;
    }

    .btn-chip i {
      font-size: 0.8rem;
    }

    .btn-chip--edit {
      background: #e5e7eb;
      color: #111827;
    }

    .btn-chip--edit:hover {
      background: #d1d5db;
    }

    .btn-chip--delete {
      background: #fee2e2;
      color: #b91c1c;
    }

    .btn-chip--delete:hover {
      background: #fecaca;
    }

    .btn-chip:active {
      transform: translateY(1px);
    }

    /* Leerer Zustand */
    .topics-table td.empty {
      text-align: center;
      color: #6b7280;
      padding: 18px 10px;
      font-style: italic;
    }
  </style>
</head>
<body>
  <!-- Globaler Admin-Header (Navigation & Benutzerinfos) -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <!-- Hauptlayout-Container für die Adminseite -->
  <div class="admin-wrapper">
    <!-- Linke Sidebar mit allen Admin-Menüpunkten -->
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

    <!-- Rechter Inhaltsbereich der Topic-Übersicht -->
    <div class="admin-content">

    <!-- Aktionen: neues Topic erstellen oder vorhandene verwalten -->
      <div class="button-group">
        <a href="create.php" class="btn btn--lg btn--primary">
          <i class="fas fa-plus"></i> Add Topic
        </a>
        <a href="index.php" class="btn btn--lg btn--ghost">
          <i class="fas fa-list"></i> Manage Topics
        </a>
      </div>

      <!-- Hauptbereich mit Topic-Tabelle und Statusmeldungen -->
      <div class="content">
        <h2 class="page-title">Manage Topics</h2>

        <!-- Systemmeldungen (Erfolg, Fehler, Hinweise) anzeigen -->
        <?php include ROOT_PATH . "/app/Support/includes/messages.php"; ?>

        <!-- Tabelle mit allen vorhandenen Topics -->
        <table class="topics-table">
          <!-- Tabellenkopf mit Spaltenüberschriften -->
          <thead>
            <tr>
              <th>SN</th>
              <th>Name</th>
              <th style="width: 220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Falls keine Topics existieren: Hinweiszeile anzeigen -->
            <?php if (empty($topics)): ?>
              <tr>
                <td class="empty" colspan="3">Keine Topics vorhanden.</td>
              </tr>
            <?php else: ?>
              <?php $sn = 1; ?>
              <!-- Alle Topics einzeln rendern (Name + Aktionen) -->
              <?php foreach ($topics as $topic): ?>
                <?php
                  $topicId   = (int)($topic['id'] ?? 0);
                  $topicName = (string)($topic['name'] ?? '');
                ?>
                <tr>
                  <td><?= $sn++ ?></td>
                  <td><?= htmlspecialchars($topicName, ENT_QUOTES, 'UTF-8') ?></td>
                  <!-- Aktionen für jedes Topic: Bearbeiten oder Löschen -->
                  <td class="table-actions">
                    <a href="edit.php?id=<?= $topicId ?>" class="btn-chip btn-chip--edit">
                      <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="index.php?del_id=<?= $topicId ?>"
                       class="btn-chip btn-chip--delete"
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

  <!-- Admin-JavaScript: Tabellenfunktionen, UI-Logik -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/resources/assets/js/scripts.js"></script>
</body>
</html>
