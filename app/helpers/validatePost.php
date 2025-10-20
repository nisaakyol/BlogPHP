<?php
/**
 * Datei: app/helpers/validatePost.php
 * Zweck: Wrapper-Funktion validatePost() → delegiert an OOP ValidationService
 *
 * Verhalten:
 * - Lädt den OOP-Bootstrap.
 * - Stellt validatePost(array $data): array bereit (nur, wenn noch nicht definiert).
 */

// Projekt-Root bestimmen (ROOT_PATH bevorzugt)
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');

// OOP-Bootstrap laden (app/OOP/bootstrap.php oder Fallback OOP/bootstrap.php)
$__candidateA = $__root . '/app/OOP/bootstrap.php';
$__candidateB = $__root . '/OOP/bootstrap.php';
$__boot = is_file($__candidateA) ? $__candidateA : $__candidateB;

if (is_file($__boot)) {
  require_once $__boot;
} else {
  die('Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . ')');
}

use App\OOP\Services\ValidationService;

/**
 * Validiere Post-Daten über den OOP-ValidationService.
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungs-Ergebnis (Fehler/Werte gemäß Service-Contract)
 */
if (!function_exists('validatePost')) {
  function validatePost(array $data): array
  {
    return ValidationService::post($data);
  }
}
