<?php
/* Zentrale Pfad-/URL-Definitionen + OOP-Bootstrap */

if (!defined('ROOT_PATH')) {
  define('ROOT_PATH', realpath(dirname(__FILE__)));
}

if (!defined('BASE_URL')) {
    // Protokoll + Host (Host enthält Port bereits, z.B. localhost:8080)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Pfad vom DocumentRoot bis zu deinem Projekt-ROOT_PATH
    $docRoot  = rtrim(str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $rootPath = rtrim(str_replace('\\','/', realpath(ROOT_PATH)), '/');

    // Falls ROOT_PATH unterhalb des DocRoots liegt, relativen Web-Pfad bilden
    $rel = '';
    if ($docRoot && str_starts_with($rootPath, $docRoot)) {
        $rel = substr($rootPath, strlen($docRoot)); // z.B. /php/BLOG Kopie
    }

    // Pfad-Segmente URL-encoden (Leerzeichen -> %20)
    $segments = array_filter(explode('/', trim($rel, '/')), 'strlen');
    $relEncoded = $segments ? '/' . implode('/', array_map('rawurlencode', $segments)) : '';

    define('BASE_URL', $scheme . '://' . $host . $relEncoded);
}

// Optional: Composer Autoload (falls du composer nutzt)
$composer = ROOT_PATH . '/vendor/autoload.php';
if (is_file($composer)) { require_once $composer; }

// OOP-Bootstrap (Autoloader für App\OOP\*)
$topBootstrap = ROOT_PATH . '/app/OOP/bootstrap.php';
if (is_file($topBootstrap)) { require_once $topBootstrap; }
