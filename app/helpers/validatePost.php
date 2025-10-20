<?php
/**
 * Datei: app/helpers/validatePost.php
 * Zweck: Wrapper für die Post-Validierung → delegiert an OOP\ValidationService
 * Kompatibilität:
 *  - validatePost($data)  // Legacy-Aufruf
 *  - validatePost($data, $files)  // neuer Aufruf
 */

# Root ermitteln
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');

# OOP-Bootstrap laden (app/OOP/bootstrap.php oder OOP/bootstrap.php)
$__candidateA = $__root . '/app/OOP/bootstrap.php';
$__candidateB = $__root . '/OOP/bootstrap.php';
$__boot = is_file($__candidateA) ? $__candidateA : $__candidateB;
if (is_file($__boot)) {
  require_once $__boot;
} else {
  die('Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter ' .
      htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ')');
}

use App\OOP\Services\ValidationService;

if (!function_exists('validatePost')) {
  /**
   * @param array $data  z. B. $_POST
   * @param array $files z. B. $_FILES (optional für Legacy-Kompatibilität)
   * @return array Liste von Fehlermeldungen (leer = OK)
   */
  function validatePost(array $data, array $files = []): array
  {
    // Falls dein ValidationService eine Methode post() hat, flexibel aufrufen
    if (class_exists(ValidationService::class) && method_exists(ValidationService::class, 'post')) {
      try {
        $ref = new \ReflectionMethod(ValidationService::class, 'post');
        if ($ref->getNumberOfParameters() >= 2) {
          return ValidationService::post($data, $files);
        }
        return ValidationService::post($data); // Service erwartet nur $data
      } catch (\Throwable $e) {
        // Fallback auf einfache Inline-Validierung
      }
    }

    // ---------- Fallback-Validierung (Minimalchecks) ----------
    $errors = [];

    $title    = trim((string)($data['title']    ?? ''));
    $body     = trim((string)($data['body']     ?? ''));
    $topic_id = (int)($data['topic_id'] ?? 0);

    if ($title === '')   $errors[] = 'Title is required';
    if ($body === '')    $errors[] = 'Body is required';
    if ($topic_id <= 0)  $errors[] = 'Topic is required';

    // Bei Create ist ein Bild Pflicht (wie dein Controller es anlegt)
    $isCreate = isset($data['add-post']);
    if ($isCreate) {
      $img = $files['image'] ?? null;
      if (!$img || empty($img['name'])) {
        $errors[] = 'Post image required';
      }
    }

    return $errors;
  }
}
