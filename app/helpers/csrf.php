<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_check(string $token): bool {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
