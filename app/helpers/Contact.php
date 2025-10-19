<?php
require_once __DIR__ . '/../../path.php'; // sorgt für ROOT_PATH / BASE_URL

$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');
$__boot = $__root . '/app/OOP/bootstrap.php';
if (is_file($__boot)) { require_once $__boot; } else { die('Autoload-Fehler: '.$__boot); }

// Composer (PHPMailer)
$__composer = $__root . '/vendor/autoload.php';
if (is_file($__composer)) { require_once $__composer; }

require_once $__root . '/app/database/db.php';           // Session/DB (Legacy kompatibel)
require_once $__root . '/app/helpers/middleware.php';    // usersOnly()

use App\OOP\Services\ContactService;

usersOnly();

if (!empty($_POST['message']) && !empty($_POST['Adresse'])) {
    $Adresse = $_POST['Adresse'];
    $message = $_POST['message'];

    ContactService::send($Adresse, $message);

    $_SESSION['message'] = "Ihre Nachricht wurde abgesandt";
    $_SESSION['type']    = "success";
    header("Location: " . BASE_URL . "/index.php");
    exit();
} else {
    header("Location: " . BASE_URL . "/index.php");
    $_SESSION['message'] = "Sie haben nicht alle Felder ausgefüllt";
    $_SESSION['type']    = "error";
}
