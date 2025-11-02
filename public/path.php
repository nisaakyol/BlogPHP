<?php
declare(strict_types=1);

// Projektwurzel (ROOT_PATH) relativ zu diesem File bestimmen
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(str_replace('\\','/', dirname(__DIR__)), '/'));
}


// BASE_URL zur Laufzeit ermitteln (beachtet HTTPS und Reverse-Proxy Header)
if (!defined('BASE_URL')) {
    // HTTPS-Erkennung: direkt, per REQUEST_SCHEME oder X-Forwarded-Proto
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['REQUEST_SCHEME'] ?? '') === 'https')
           || (stripos((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''), 'https') !== false);
    $scheme = $https ? 'https' : 'http';

    // Host/Port aus Request bzw. Proxy-Headern ermitteln
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $port = $_SERVER['HTTP_X_FORWARDED_PORT'] ?? ($_SERVER['SERVER_PORT'] ?? null);
    if ($port && !in_array((int)$port, [80, 443], true) && strpos($host, ':') === false) {
        $host .= ':' . $port;
    }

    // Pfadanteil relativ zum Document Root ableiten
    $docRoot  = rtrim(str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $rootPath = rtrim(str_replace('\\','/', ROOT_PATH), '/');

    $rel = '';
    if ($docRoot && str_starts_with($rootPath, $docRoot)) {
        $rel = substr($rootPath, strlen($docRoot)); 
    }

    // Segmente URL-encoden und zusammensetzen
    $segments   = array_filter(explode('/', trim($rel, '/')), 'strlen');
    $relEncoded = $segments ? '/' . implode('/', array_map('rawurlencode', $segments)) : '';

    // BASE_URL endgültig definieren (Schema + Host[:Port] + Relativpfad)
    define('BASE_URL', $scheme . '://' . $host . $relEncoded);
}


// reCAPTCHA v3 (unsichtbar) – Werte für Kommentar-Formular setzen
putenv('RECAPTCHA_V3_SITE=6LenCforAAAAAMs2WzBlWMUY7Ubf1gpaawzcPdNs');
putenv('RECAPTCHA_V3_SECRET=6LenCforAAAAAHSaFp6zx6OY6VMtz8PkkdkF4cG4');

// reCAPTCHA v2 (Checkbox) – Werte für Login-Formular setzen
putenv('RECAPTCHA_V2_SITE=6LcfGforAAAAACjqcOTspM2-7Nxl4wq78ShTGpBp');
// Achtung: Schlüsselname wirkt vertippt – vermutlich RECAPTCHA_V2_SECRET gemeint
putenv('RECAPTCHA_SV2_ECRET=6LcfGforAAAAAD4Blqgn4u0CjyyxpUbnG_NaiFDF');
putenv('RECAPTCHA_MIN_SCORE=0.5');
putenv('RECAPTCHA_MIN_SCORE_LOGIN=0.7');

// Standard-DB-Konfiguration (z. B. für Docker-Compose)
if (!defined('DB_HOST')) define('DB_HOST', 'db');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'blog');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Mail-Defaults (Dev: Mailpit; Prod: SMTP-Daten setzen)
if (!defined('MAIL_HOST'))      define('MAIL_HOST', 'localhost');
if (!defined('MAIL_PORT'))      define('MAIL_PORT', 1025);
if (!defined('MAIL_SECURE'))    define('MAIL_SECURE', '');
if (!defined('MAIL_USER'))      define('MAIL_USER', '');
if (!defined('MAIL_PASS'))      define('MAIL_PASS', '');
if (!defined('MAIL_FROM'))      define('MAIL_FROM', 'no-reply@example.com');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'Blog');
if (!defined('MAIL_TO'))        define('MAIL_TO', 'admin@example.com');

// Secret für signierte Links (Preview/Aktivierung). In Prod als langen Zufallswert setzen
if (!defined('EMAIL_LINK_SECRET')) define('EMAIL_LINK_SECRET', 'CHANGE-ME-TO-LONG-RANDOM');
