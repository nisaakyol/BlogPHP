<?php
declare(strict_types=1);

namespace App\OOP\Services;

/**
 * UploadService
 *
 * Verschiebt ein hochgeladenes Bild in den Ordner
 * {ROOT_PATH}/assets/images und liefert [bool $ok, ?string $imageName] zurück.
 * Fehlermeldungen werden nicht geworfen; die aufrufende Stelle prüft $ok.
 */
class UploadService
{
    /**
     * Verschiebt das hochgeladene Bild nach ROOT_PATH/assets/images.
     * Gibt [bool $ok, string $imageName] zurück.
     *
     * @param string $rootPath Basisverzeichnis (ROOT_PATH)
     * @param array  $file     Eintrag aus $_FILES['image']
     * @return array{0: bool, 1: string}
     */
    public static function moveImage(string $rootPath, array $file): array
    {
        $imageName   = time() . '_' . $file['name'];
        $destination = rtrim($rootPath, '/') . '/assets/images/' . $imageName;

        $ok = move_uploaded_file($file['tmp_name'], $destination);

        return [$ok, $imageName];
    }
}
