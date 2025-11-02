<?php
declare(strict_types=1);

// Zweck: Sicheres Verschieben von hochgeladenen Bildern in das Projekt (Whitelisting, Größenlimit, saubere Dateinamen, Zielordner-Prüfung)

namespace App\Infrastructure\Services;

final class UploadService
{
    // erlaubte Dateiendungen
    private const ALLOWED_EXT = ['jpg','jpeg','png','gif','webp'];
    // erlaubte MIME-Types (Server-seitig geprüft)
    private const ALLOWED_MIME = [
        'image/jpeg','image/png','image/gif','image/webp'
    ];
    // maximales Upload-Volumen (z. B. 5 MiB)
    private const MAX_BYTES = 5 * 1024 * 1024;

    // Standard-Unterordner für öffentliche Bilder relativ zu ROOT_PATH
    // Hinweis: Dein Frontend nutzt meist /public/resources/assets/images/
    private const DEFAULT_PUBLIC_IMG_DIR = '/public/resources/assets/images';

    
     // Verschiebt das hochgeladene Bild in den Zielordner.
    public static function moveImage(string $rootPath, array $file, string $subdir = self::DEFAULT_PUBLIC_IMG_DIR): array
    {
        // 1) Grundlegende Upload-Fehler prüfen
        if (!isset($file['error'], $file['tmp_name'], $file['name'], $file['size'])) {
            return [false, ''];
        }
        if ($file['error'] !== \UPLOAD_ERR_OK) {
            return [false, ''];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return [false, ''];
        }

        // 2) Größenlimit prüfen
        $size = (int)$file['size'];
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return [false, ''];
        }

        // 3) Endung und MIME-Type whitelisten
        $origName = (string)$file['name'];
        $ext = strtolower(pathinfo($origName, \PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return [false, ''];
        }

        // MIME via finfo ermitteln (robuster als $_FILES['type'])
        $mime = self::detectMime($file['tmp_name']);
        if ($mime === null || !in_array($mime, self::ALLOWED_MIME, true)) {
            return [false, ''];
        }

        // 4) Zielverzeichnis vorbereiten
        $targetDir = rtrim(str_replace('\\','/', $rootPath), '/') . $subdir;
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                return [false, ''];
            }
        }

        // 5) Sicheren Dateinamen erzeugen (keine Originalnamen verwenden)
        //    time() als Präfix + 8 rand Bytes + gesäuberte Kurzform des Originalnamens
        $safeStem = self::slugStem(pathinfo($origName, \PATHINFO_FILENAME));
        $rand = bin2hex(random_bytes(8));
        $imageName = sprintf('%d_%s_%s.%s', time(), $rand, $safeStem, $ext);

        // 6) Datei verschieben
        $destination = $targetDir . '/' . $imageName;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            return [false, ''];
        }

        // 7) Rechte etwas konservativ setzen
        @chmod($destination, 0644);

        // Erfolg
        return [true, $imageName];
    }

    // MIME-Erkennung über finfo (Fallback: null)
    private static function detectMime(string $tmpFile): ?string
    {
        if (!is_file($tmpFile)) return null;
        $fi = new \finfo(\FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmpFile);
        return is_string($mime) ? $mime : null;
        // Optional könnte man zusätzlich getimagesize() prüfen, um Bilddimensionen zu validieren
    }

    // Aus dem Dateistamm einen kurzen, URL-tauglichen „Slug“ erzeugen
    private static function slugStem(string $stem, int $maxLen = 36): string
    {
        $s = mb_strtolower(trim($stem));
        // Umlaute/Diakritika vereinfachen
        $s = strtr($s, [
            'ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss',
            'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','å'=>'a','ā'=>'a',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','ē'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ī'=>'i',
            'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ø'=>'o','ō'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'ue','ū'=>'u',
            'ç'=>'c','ñ'=>'n'
        ]);
        // Nicht erlaubte Zeichen durch Bindestrich ersetzen
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
        $s = trim($s, '-');
        if ($s === '') $s = 'img';
        if (mb_strlen($s) > $maxLen) {
            $s = mb_substr($s, 0, $maxLen);
            $s = rtrim($s, '-');
        }
        return $s;
    }
}
