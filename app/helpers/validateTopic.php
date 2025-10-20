<?php
/**
 * Datei: app/helpers/validateTopic.php
 * Zweck: Wrapper-Funktion validateTopic() → delegiert an OOP ValidationService
 *
 * Verhalten:
 * - Lädt den OOP-Bootstrap.
 * - Stellt validateTopic(array $data): array bereit (nur, wenn noch nicht definiert).
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
  die(
    'Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter ' .
    htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') .
    ')'
  );
}

use App\OOP\Services\ValidationService;

/**
 * Validiert Topic-Daten über den OOP-ValidationService.
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungsergebnis laut Service-Contract
 */
if (!function_exists('validateTopic')) {
  function validateTopic(array $data): array
  {
    return ValidationService::topic($data);
  }
}
