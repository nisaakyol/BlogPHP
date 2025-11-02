<?php
// Zweck: Kleine, sichere Helper zum Setzen/Auslesen von Cookies (Secure, HttpOnly, SameSite=Lax)

declare(strict_types=1);

function set_cookie_safe(string $name, string $value, int $ttlSeconds): void {
  // Wenn Header schon gesendet wurden, kein Cookie mehr möglich
  if (headers_sent()) return;

  // Secure auch hinter Proxies korrekt erkennen
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['REQUEST_SCHEME'] ?? '') === 'https')
          || (stripos((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''), 'https') !== false);

  // Cookie-Parameter zentral setzen
  $params = [
    'expires'  => time() + $ttlSeconds, // Ablaufzeit
    'path'     => '/',                  // vollständig gültig
    'secure'   => $isHttps,             // nur via HTTPS senden
    'httponly' => true,                 // nicht per JS zugreifbar
    'samesite' => 'Lax',                // schützt vor CSRF bei Top-Level-Navigation ok
  ];

  setcookie($name, $value, $params);
}

function get_cookie(string $name): ?string {
  // Liefert den Cookie-Wert oder null, falls nicht vorhanden
  return array_key_exists($name, $_COOKIE) ? (string)$_COOKIE[$name] : null;
}
