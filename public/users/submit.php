<?php
// Initiales Setup: Projektpfade, Bootstrap, Middleware und benötigte Klassen laden
declare(strict_types=1);
require __DIR__ . '/../path.php';
require_once ROOT_PATH . '/app/Support/includes/bootstrap.php';
require_once ROOT_PATH . '/app/Support/helpers/middleware.php';
require_once ROOT_PATH . '/app/Infrastructure/Repositories/DbRepository.php';
require_once ROOT_PATH . '/app/Http/Controllers/PostWriteController.php';

// Controller & Repository für Post-Aktionen importieren (Submit, Edit, etc.)
use App\Http\Controllers\PostWriteController;
use App\Infrastructure\Repositories\DbRepository;

// Zugriffsschutz: nur eingeloggte Benutzer dürfen Beiträge einreichen
usersOnly();
// Post-ID aus Anfrage lesen und validieren, ansonsten zurück zum Dashboard
$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) { header('Location: ' . BASE_URL . '/public/users/dashboard.php?tab=posts'); exit; }

// Post an den Controller übergeben und zur Prüfung einreichen
(new PostWriteController(new DbRepository()))->submit($postId);
