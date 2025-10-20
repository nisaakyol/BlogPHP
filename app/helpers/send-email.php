<?php
/**
 * Datei: app/helpers/send-email.php
 * Zweck: Mailversand initialisieren (Autoload/Composer laden) und MailerService ausführen.
 *
 * Erwartung:
 * - $information kommt aus dem aufrufenden Scope und ist idealerweise ein Array.
 * - Optional: /config/mail.php kann ['to' => '...'] enthalten.
 */

$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');

// OOP-Bootstrap laden
$__boot = $__root . '/OOP/bootstrap.php';
if (is_file($__boot)) {
  require_once $__boot;
} else {
  die('Autoload-Fehler: ' . htmlspecialchars($__boot, ENT_QUOTES, 'UTF-8'));
}

// Composer-Autoload (PHPMailer etc.)
$__composer = $__root . '/vendor/autoload.php';
if (is_file($__composer)) {
  require_once $__composer;
}

use App\OOP\Services\MailerService;

// Optionale Mail-Konfiguration
$__cfg = $__root . '/config/mail.php';
if (is_file($__cfg)) {
  $cfg = require $__cfg;
  if (is_array($cfg) && isset($cfg['to']) && !defined('MAIL_TO')) {
    define('MAIL_TO', (string)$cfg['to']);
  }
}

// Payload absichern
$payload = (isset($information) && is_array($information)) ? $information : [];

// Senden (Fehlerbehandlung im Service)
MailerService::send($payload);
