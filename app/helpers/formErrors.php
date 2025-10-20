<?php
/**
 * Datei: app/helpers/formErrors.php
 * Zweck: Robuste Fehlerliste für Formulare
 *
 * Verhalten:
 * - Nimmt $errors aus dem aktuellen Scope, sonst $_SESSION['form_errors'].
 * - Akzeptiert String oder Array; trimmt leere, dedupliziert, erhält Reihenfolge.
 * - HTML-escaped Ausgabe, ARIA-Attribute für Screenreader.
 * - Räumt $_SESSION['form_errors'] nach Ausgabe auf (Einmal-Anzeige).
 */

// Quelle ermitteln
$rawErrs = $errors ?? ($_SESSION['form_errors'] ?? []);

// In Array verwandeln
if (!is_array($rawErrs)) {
  $rawErrs = [$rawErrs];
}

// Normalisieren: cast, trim, leere raus
$norm = [];
foreach ($rawErrs as $e) {
  $s = trim((string)$e);
  if ($s !== '') {
    $norm[] = $s;
  }
}

// Deduplizieren, Reihenfolge erhalten
$seen = [];
$errs = [];
foreach ($norm as $s) {
  if (!isset($seen[$s])) {
    $seen[$s] = true;
    $errs[] = $s;
  }
}

// Ausgabe, wenn vorhanden
if (count($errs) > 0): ?>
  <div class="msg error" role="alert" aria-live="assertive" tabindex="-1">
    <ul>
      <?php foreach ($errs as $err): ?>
        <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php
endif;

// Session-Fehler nach Anzeige leeren (nur wenn wir sie verwendet haben)
if (!isset($errors) && isset($_SESSION['form_errors'])) {
  unset($_SESSION['form_errors']);
}
