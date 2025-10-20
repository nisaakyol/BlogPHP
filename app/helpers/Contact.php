<?php
/**
 * Datei: app/actions/contact/send.php (Beispielpfad)
 * Zweck: Kontaktformular verarbeiten und Nachricht via ContactService versenden
 *
 * Hinweise:
 * - Zugriff nur für eingeloggte Benutzer (usersOnly()).
 * - Erwartet POST-Felder: Adresse (E-Mail) und message.
 * - Setzt eine Flash-Message in $_SESSION und leitet zur Startseite um.
 * - Empfehlung: CSRF-Token ergänzen.
 */

// Basis-Pfade/Konstanten (ROOT_PATH, BASE_URL)
require_once __DIR__ . '/../../path.php';

// Optionaler OOP-Bootstrap (Autoloader)
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');
$__boot = $__root . '/app/OOP/bootstrap.php';
if (is_file($__boot)) {
  require_once $__boot;
} else {
  die('Autoload-Fehler: ' . $__boot);
}

// Composer-Autoload (PHPMailer etc.)
$__composer = $__root . '/vendor/autoload.php';
if (is_file($__composer)) {
  require_once $__composer;
}

// DB/Session (Legacy-kompatibel) & Middleware
require_once $__root . '/app/database/db.php';
require_once $__root . '/app/helpers/middleware.php';

use App\OOP\Services\ContactService;

// Zugriffsschutz
usersOnly();

// Nur POST-Requests verarbeiten
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  $_SESSION['message'] = 'Ungültige Anfragemethode.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/index.php');
  exit();
}

// Eingaben trimmen
$adresse = trim($_POST['Adresse'] ?? '');
$message = trim($_POST['message'] ?? '');

// Minimal-Validierung (Adresse als E-Mail interpretiert)
if ($adresse === '' || $message === '') {
  $_SESSION['message'] = 'Sie haben nicht alle Felder ausgefüllt.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/index.php');
  exit();
}

// Wenn als E-Mail gedacht, validieren (falls es eine Postanschrift sein soll, diese Prüfung entfernen)
if (!filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
  $_SESSION['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/index.php');
  exit();
}

// Versand versuchen
try {
  ContactService::send($adresse, $message);

  $_SESSION['message'] = 'Ihre Nachricht wurde abgesandt.';
  $_SESSION['type']    = 'success';
  header('Location: ' . BASE_URL . '/index.php');
  exit();
} catch (Throwable $e) {
  // In Produktion zusätzlich loggen
  $_SESSION['message'] = 'Versand fehlgeschlagen. Bitte später erneut versuchen.';
  $_SESSION['type']    = 'error';
  header('Location: ' . BASE_URL . '/index.php');
  exit();
}
