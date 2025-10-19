<?php
// Empfohlene sichere Cookie-Parameter (passen für localhost)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',          // GANZE SITE
    'secure'   => false,        // auf HTTPS => true
    'httponly' => true,
    'samesite' => 'Lax',        // für normale Redirects OK
]);

// Falls der Container Probleme mit dem Session-Speicher hat:
if (!ini_get('session.save_path')) {
    ini_set('session.save_path', sys_get_temp_dir());
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!defined('TOP_BOOTSTRAP_LOADED')) {
  define('TOP_BOOTSTRAP_LOADED', true);
  spl_autoload_register(function($class){
    $prefix = 'App\\OOP\\'; if (strncmp($class,$prefix,strlen($prefix))!==0) return;
    $file = __DIR__ . '/' . str_replace('\\','/',substr($class,strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
  });
  if (!function_exists('top_db_config')) {
    function top_db_config(): array {
      $file = __DIR__ . '/../../config/db.php';
      return is_file($file) ? require $file : [
        'dsn'=>'mysql:host=127.0.0.1;dbname=blog;charset=utf8mb4','user'=>'root','pass'=>''
      ];
    }
  }
}
if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof mysqli)) {
    $__connect = ROOT_PATH . '/app/database/connect.php';
    if (is_file($__connect)) {
        require_once $__connect;
    }
}
