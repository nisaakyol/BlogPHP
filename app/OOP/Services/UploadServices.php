<?php
namespace App\OOP\Services;

class UploadService {
    /**
     * Verschiebt das hochgeladene Bild nach ROOT_PATH/assets/images.
     * Gibt [bool $ok, ?string $imageName] zurück. Fehlermeldungen pusht der Aufrufer.
     */
    public static function moveImage(string $rootPath, array $file): array {
        $imageName = time() . '_' . $file['name'];
        $destination = rtrim($rootPath,'/') . "/assets/images/" . $imageName;
        $ok = move_uploaded_file($file['tmp_name'], $destination);
        return [$ok, $imageName];
    }
}
