<?php
/**
 * Datei: users/dashboard.php
 * Zweck: Einstiegsroute fürs (Admin-)Dashboard.
 *
 * Verhalten:
 * - Lädt Projektpfade (ROOT_PATH, BASE_URL) und den Admin-Bootstrap.
 * - Blockiert Nicht-Admins (Normale User dürfen nur kommentieren, kein Dashboard).
 * - Leitet Admins sauber ins eigentliche Admin-Dashboard weiter.
 */

require_once __DIR__ . '/../path.php';                 // stellt ROOT_PATH / BASE_URL bereit
require_once ROOT_PATH . '/admin/_admin_boot.php';     // zentraler Admin-Bootstrap (Guards, Session, etc.)

// ---- Zugriffsschutz ----
// Falls adminOnly() existiert, nutze sie. Sonst Fallback mit direkter Prüfung.
if (function_exists('adminOnly')) {
    adminOnly(); // setzt ggf. Message + Redirect selbst
} else {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['admin'])) {
        // Nicht-Admin → zurück zur Startseite
        $_SESSION['message'] = 'Zugriff verweigert: Admin-Rechte erforderlich.';
        $_SESSION['type']    = 'error';
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// ---- Admin: weiterleiten ins eigentliche Dashboard ----
// Passe das Ziel an deine Struktur an. Häufig: /admin/index.php oder /admin/dashboard.php
$target = '/admin/index.php';
if (!is_file(ROOT_PATH . $target)) {
    // Fallback: Post-Übersicht
    $target = '/admin/posts/index.php';
}
header('Location: ' . BASE_URL . $target);
exit;



