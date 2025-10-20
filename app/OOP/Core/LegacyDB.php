<?php
declare(strict_types=1);

namespace App\OOP\Core;

use mysqli;

/**
 * LegacyDB
 *
 * Stellt die bestehende mysqli-Verbindung aus dem Legacy-Stack bereit.
 * Erwartet, dass $GLOBALS['conn'] als mysqli-Instanz initialisiert wurde
 * (z. B. in app/database/db.php bzw. connect.php).
 */
class LegacyDB
{
    /**
     * Liefert die bestehende mysqli-Connection ($GLOBALS['conn']).
     *
     * @return mysqli
     */
    public static function conn(): mysqli
    {
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            return $GLOBALS['conn'];
        }

        // Verhalten beibehalten: hart abbrechen, wenn keine Verbindung vorhanden ist.
        die('Database connection error: no mysqli $conn available in LegacyDB');
    }
}
