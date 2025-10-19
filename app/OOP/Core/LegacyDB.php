<?php
namespace App\OOP\Core;

use mysqli;

class LegacyDB {
    /** Liefert die bestehende mysqli-Connection ($GLOBALS['conn']) */
    public static function conn(): mysqli {
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            return $GLOBALS['conn'];
        }
        die('Database connection error: no mysqli $conn available in LegacyDB');
    }
}

