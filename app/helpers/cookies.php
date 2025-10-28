<?php
declare(strict_types=1);

function set_cookie_safe(string $name, string $value, int $ttlSeconds): void {
  if (headers_sent()) return;
  $params = [
    'expires'  => time() + $ttlSeconds,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
  ];
  setcookie($name, $value, $params);
}

function get_cookie(string $name): ?string {
  return isset($_COOKIE[$name]) ? (string)$_COOKIE[$name] : null;
}
