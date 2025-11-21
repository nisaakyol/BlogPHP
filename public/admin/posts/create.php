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

// kleiner HTML-Escaper (in dieser View lokal definieren)
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$ctrl = new AdminPostController(new DbRepository());
$vm   = $ctrl->handleCreate($_POST, $_FILES);

// ViewModel entpacken (Defaults, alles streng typisieren)
$errors         = $vm['errors']          ?? [];
$title          = (string)($vm['title']  ?? '');
$body           = (string)($vm['body']   ?? '');
$topic_id       = (string)($vm['topic_id'] ?? '');
$published      = !empty($vm['published']);
$topics         = is_array($vm['topics'] ?? null) ? $vm['topics'] : [];

// NEU: Felder für Bildtexte aus dem ViewModel ziehen (oder leer)
$image_alt      = (string)($vm['image_alt'] ?? '');
$image_caption  = (string)($vm['image_caption'] ?? '');
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/assets/css/admin.css">
  <title>Admin Section – Add Post</title>

  <style>
    /* ============================
       Grundlayout & Hintergrund
       (wie bei Manage Posts)
    ============================ */
    html,
    body {
      background: #efe7dd !important; /* Sand */
      margin: 0;
      padding: 0;
    }

    .admin-wrapper {
      background: #efe7dd;
      min-height: calc(100vh - 66px);
      display: flex;
    }

    .admin-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 20px 40px 40px;
      box-sizing: border-box;
    }

    .admin-content .content {
      width: 100%;
      max-width: 1200px;
      margin-top: 10px;
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 18px 45px rgba(0, 0, 0, 0.08);
      padding: 24px 32px 32px;
    }

    .page-title {
      text-align: center;
      margin: 0 0 18px;
      font-size: 1.9rem;
      font-weight: 700;
    }

    /* ============================
       Button-Gruppe oben
       (Add Post / Manage Posts)
    ============================ */
    .button-group {
  display: flex;
  flex-direction: column; 
  gap: 10px;

  position: relative;
  left: -40px;         /* ← GENAU DAS schiebt beide Buttons zusammen nach links */

  margin-top: 20px;
  margin-bottom: 18px;
}
    .button-group .btn.btn-big { 
    display: inline-flex;
    align-items: center; 
    gap: 8px; 
    padding: 0.55rem 1.3rem; 
    border-radius: 999px; 
    font-size: 0.95rem; 
    background: #d2cddc; 
    border: 1px solid rgba(0,0,0,0.1); 
    color: #fcfafaff !important; 
    text-decoration: none; 
  
  } .button-group .btn.btn-big:hover { 
    background: #030410; 
    color: #ffffff !important; }:


    /* Formular-Gruppen etwas schöner */
    form > div {
      margin-bottom: 1rem;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: .35rem;
    }

    .text-input,
    textarea,
    select,
    input[type="file"],
    input[type="text"] {
      width: 100%;
      box-sizing: border-box;
    }

    @media (max-width: 900px) {
      .admin-content {
        padding: 16px 10px 30px;
      }
      .admin-content .content {
        padding: 18px 16px 24px;
      }
      .button-group {
        justify-content: center;
      }
      .page-title {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<!-- Admin-Header einbinden (Navigation & User-Infos) -->
<body>
  <!-- Admin-Sidebar mit Menüpunkten laden -->
  <?php include ROOT_PATH . "/public/admin/adminHeader.php"; ?>

  <div class="admin-wrapper">
    <?php include ROOT_PATH . "/public/admin/adminSidebar.php"; ?>

<!-- Arbeitsbereich der Admin-Seite (rechte Spalte) -->
    <div class="admin-content">

    <!-- Schnellzugriff: Post erstellen oder Posts verwalten -->
      <div class="button-group">
        <a href="create.php" class="btn btn-big">Add Post</a>
        <a href="index.php" class="btn btn-big">Manage Posts</a>
      </div>

      <!-- Inhalt des Create-Forms, optisch eingerahmt -->
      <div class="content">
        <h2 class="page-title">Create Posts</h2>

        <!-- Validierungsfehler aus dem Controller anzeigen -->
        <?php include ROOT_PATH . "/app/Support/helpers/formErrors.php"; ?>

        <!-- Formular zum Erstellen eines neuen Blog-Posts -->
        <form action="create.php" method="post" enctype="multipart/form-data">
          <div>
            <label for="title">Title</label>
            <input
              type="text"
              id="title"
              name="title"
              class="text-input"
              value="<?= $e($title) ?>"
              required
            >
          </div>

          <!-- Hauptinhalt des Posts (wird per WYSIWYG bearbeitet) -->
          <div>
            <label for="body">Body</label>
            <textarea
              name="body"
              id="body"
              class="text-input text-input--multiline"
            ><?= $e($body) ?></textarea>
          </div>

          <div>
            <label for="image">Image</label>
            <input type="file" id="image" name="image" class="text-input" accept="image/*">
          </div>

          <!-- NEU: Bildbeschreibung (ALT) & Bildunterschrift -->
          <div class="form-group">
            <label for="image_alt">Bildbeschreibung (ALT-Text) *</label>
            <input
              id="image_alt"
              name="image_alt"
              type="text"
              value="<?= $e($image_alt) ?>"
              maxlength="200"
              required
            >
          </div>
<!-- Sichtbare Bildunterschrift unter dem Artikelbild -->
          <div class="form-group">
            <label for="image_caption">Bildunterschrift (sichtbar)</label>
            <input
              id="image_caption"
              name="image_caption"
              type="text"
              value="<?= $e($image_caption) ?>"
              maxlength="300"
            >
          </div>
<!-- Sichtbare Bildunterschrift unter dem Artikelbild -->
          <div>
            <label for="topic_id">Topic</label>
            <select id="topic_id" name="topic_id" class="text-input">
              <option value=""></option>
              <!-- Themenliste dynamisch aufbauen und aktuelle Auswahl markieren -->
              <?php foreach ($topics as $topic): ?>
                <?php
                  $tid   = (int)($topic['id'] ?? 0);
                  $tname = (string)($topic['name'] ?? '');
                  $sel   = ((string)$tid === $topic_id) ? 'selected' : '';
                ?>
                <option value="<?= $tid ?>" <?= $sel ?>><?= $e($tname) ?></option>
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
<!-- Formular absenden und neuen Post anlegen -->
          <div>
            <button type="submit" name="add-post" class="btn btn-big">Add Post</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<!-- Projekt-spezifische Admin-Skripte -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/12.2.0/classic/ckeditor.js"></script>
  <script src="../../assets/js/scripts.js"></script>
</body>
</html>
