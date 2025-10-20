<?php
/**
 * Datei: app/helpers/validateUser.php
 * Zweck: Wrapper-Funktionen für die User-Validierung → delegieren an OOP ValidationService
 *
 * Verhalten:
 * - Lädt den OOP-Bootstrap.
 * - Stellt validateUser(array $data) und validateLogin(array $data) bereit
 *   (nur, wenn die Funktionen nicht bereits existieren).
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
 * Validiert Daten für das Anlegen/Aktualisieren eines Users.
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungsergebnis gemäß Service-Contract
 */
if (!function_exists('validateUser')) {
  function validateUser(array $data): array
  {
    return ValidationService::user($data);
  }
}

/**
 * Validiert Login-Daten (z. B. auf Pflichtfelder/Format).
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungsergebnis gemäß Service-Contract
 */
if (!function_exists('validateLogin')) {
  function validateLogin(array $data): array
  {
    return ValidationService::login($data);
  }
}
