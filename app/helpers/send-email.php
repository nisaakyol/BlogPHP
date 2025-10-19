<?php
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');
$__boot = $__root . '/app/OOP/bootstrap.php';
if (is_file($__boot)) { require_once $__boot; } else { die('Autoload-Fehler: '.$__boot); }

// Composer-Autoload (PHPMailer)
$__composer = $__root . '/vendor/autoload.php';
if (is_file($__composer)) { require_once $__composer; }

use App\OOP\Services\MailerService;

// optional: config/mail.php
$__cfg = $__root . '/config/mail.php';
if (is_file($__cfg)) {
    $cfg = require $__cfg;
    if (isset($cfg['to']) && !defined('MAIL_TO')) define('MAIL_TO', $cfg['to']);
}

// $information kommt aus dem aufrufenden Scope
MailerService::send(isset($information) && is_array($information) ? $information : []);
