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
// ^ Prüft zuerst, ob eine globale Konstante ROOT_PATH existiert (konfigurierter Projekt-Root).
//   Falls nicht, wird der Root relativ zu dieser Datei ermittelt: zwei Ebenen nach oben ausgehend von app/helpers.

// OOP-Bootstrap laden (app/OOP/bootstrap.php oder Fallback OOP/bootstrap.php)
$__candidateA = $__root . '/app/OOP/bootstrap.php'; // Primärer Pfad, wenn OOP unter app/ liegt.
$__candidateB = $__root . '/OOP/bootstrap.php';     // Alternativer Pfad, wenn OOP auf Root-Ebene liegt.
$__boot = is_file($__candidateA) ? $__candidateA : $__candidateB; // Bevorzugt A, sonst B (ohne Existenzprüfung von B an dieser Stelle).

if (is_file($__boot)) {
  require_once $__boot; // Lädt einmalig den Autoloader/Bootstrap der OOP-Schicht (Namespaces, Services, Configs).
} else {
  // Harte Abbruchstrategie: Wenn kein Bootstrap gefunden wird, Anwendung mit klarer Fehlermeldung stoppen.
  die(
    'Autoload-Fehler: OOP/bootstrap.php nicht gefunden (gesucht unter ' .
    htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8') . // Ausgabe sicher encodieren (XSS-Prävention im Fehlerfall).
    ')'
  );
}

use App\OOP\Services\ValidationService; // Bindet den Namespaced-Service ein, der die eigentliche Validierung kapselt.

/**
 * Validiert Daten für das Anlegen/Aktualisieren eines Users.
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungsergebnis gemäß Service-Contract
 */
if (!function_exists('validateUser')) { // Schutz vor Doppeldefinition, falls Helper mehrfach eingebunden wird.
  function validateUser(array $data): array
  {
    return ValidationService::user($data); // Delegation: ruft die statische Methode im OOP-Service auf.
  }
}

/**
 * Validiert Login-Daten (z. B. auf Pflichtfelder/Format).
 *
 * @param array $data Eingabedaten (z. B. $_POST)
 * @return array      Validierungsergebnis gemäß Service-Contract
 */
if (!function_exists('validateLogin')) { // Ebenfalls Idempotenz: nur definieren, wenn noch nicht vorhanden.
  function validateLogin(array $data): array
  {
    return ValidationService::login($data); // Delegation: Auslagerung der Logik an den OOP-Validierungsservice.
  }
}
