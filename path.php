<?php
/* Zentrale Pfad-/URL-Definitionen + OOP-Bootstrap */

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
