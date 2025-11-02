<?php
// Zweck: Fehler aus $errors oder $_SESSION['form_errors'] normalisieren, deduplizieren und als Liste rendern

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
