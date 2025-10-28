<?php
// reCAPTCHA v3 (unsichtbar) → für Kommentare
putenv('RECAPTCHA_V3_SITE=6LenCforAAAAAMs2WzBlWMUY7Ubf1gpaawzcPdNs');
putenv('RECAPTCHA_V3_SECRET=6LenCforAAAAAHSaFp6zx6OY6VMtz8PkkdkF4cG4');

// reCAPTCHA v2 (Checkbox) → für Login
putenv('RECAPTCHA_V2_SITE=6LcfGforAAAAACjqcOTspM2-7Nxl4wq78ShTGpBp');
putenv('RECAPTCHA_SV2_ECRET=6LcfGforAAAAAD4Blqgn4u0CjyyxpUbnG_NaiFDF');
putenv('RECAPTCHA_MIN_SCORE=0.5');
putenv('RECAPTCHA_MIN_SCORE_LOGIN=0.7');

if (!defined('ROOT_PATH')) {
  define('ROOT_PATH', realpath(dirname(__FILE__)));
  // ROOT_PATH: absoluter Serverpfad zum Projekt-Root (Verzeichnis dieser Datei).
  // realpath + dirname(__FILE__) → robust gegen relative Aufrufe/Symlinks.
}

if (!defined('BASE_URL')) {
    // BASE_URL dynamisch bestimmen (Schema + Host [+ evtl. Unterordner])

    // Protokoll + Host (Host enthält Port bereits, z.B. localhost:8080)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    // ^ Wenn HTTPS aktiv ist → 'https', sonst 'http'.
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // ^ Servername inkl. Port (Fallback: 'localhost').

    // Pfad vom DocumentRoot bis zu deinem Projekt-ROOT_PATH
    $docRoot  = rtrim(str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $rootPath = rtrim(str_replace('\\','/', realpath(ROOT_PATH)), '/');
    // ^ Beide Pfade normalisieren (Slashes vereinheitlichen, End-Slash entfernen).

    // Falls ROOT_PATH unterhalb des DocRoots liegt, relativen Web-Pfad bilden
    $rel = '';
    if ($docRoot && str_starts_with($rootPath, $docRoot)) {
        $rel = substr($rootPath, strlen($docRoot)); // z.B. /php/BLOG Kopie
        // ^ Teil nach dem DocumentRoot → URL-Pfadsegment(e).
    }

    // Pfad-Segmente URL-encoden (Leerzeichen -> %20)
    $segments = array_filter(explode('/', trim($rel, '/')), 'strlen');
    $relEncoded = $segments ? '/' . implode('/', array_map('rawurlencode', $segments)) : '';
    // ^ Segmentweises rawurlencode verhindert ungültige URLs bei Sonderzeichen.

    define('BASE_URL', $scheme . '://' . $host . $relEncoded);
    // Ergebnis: z.B. https://example.com[/unter/ordner]
}

// Optional: Composer Autoload (falls du composer nutzt)
$composer = ROOT_PATH . '/vendor/autoload.php';
if (is_file($composer)) { require_once $composer; }
// ^ Lädt Vendor-Autoloader nur, wenn vorhanden (keine harte Abhängigkeit).

// OOP-Bootstrap (Autoloader für App\OOP\*)
$topBootstrap = ROOT_PATH . '/app/OOP/bootstrap.php';
if (is_file($topBootstrap)) { require_once $topBootstrap; }
// ^ Initialisiert Klassenautoloader/Bootstrap der OOP-Schicht, sofern vorhanden.
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'db');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'blog');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'bloguser');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'blogpass');


if (!defined('MAIL_HOST'))        define('MAIL_HOST', 'mail-1');   // Mailpit/SMTP Host
if (!defined('MAIL_PORT'))        define('MAIL_PORT', 1025);          // Mailpit Port
if (!defined('MAIL_SECURE'))      define('MAIL_SECURE', '');          // '', 'ssl' oder 'tls'
if (!defined('MAIL_USER'))        define('MAIL_USER', '');            // leer für Mailpit
if (!defined('MAIL_PASS'))        define('MAIL_PASS', '');            // leer für Mailpit
if (!defined('MAIL_FROM'))        define('MAIL_FROM', 'no-reply@example.com');
if (!defined('MAIL_FROM_NAME'))   define('MAIL_FROM_NAME', 'Blog');
if (!defined('MAIL_TO'))          define('MAIL_TO', 'admin@example.com');

// Geheimnis für signierte E-Mail-Links (lang & zufällig wählen!)
if (!defined('EMAIL_LINK_SECRET')) define('EMAIL_LINK_SECRET', 'CHANGE-THIS-TO-A-LONG-RANDOM-STRING');
