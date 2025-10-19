<?php
$__root = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../..');
$__boot = $__root . '/app/OOP/bootstrap.php';
if (is_file($__boot)) { require_once $__boot; } else { die('Autoload-Fehler: '.$__boot); }

use App\OOP\Services\ValidationService;

if (!function_exists('validateUser'))  { function validateUser(array $d): array { return ValidationService::user($d); } }
if (!function_exists('validateLogin')) { function validateLogin(array $d): array { return ValidationService::login($d); } }
