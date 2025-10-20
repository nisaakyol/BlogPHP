<?php
/**
 * Datei: app/includes/messages.php
 * Zweck: Flash-Messages aus der Session anzeigen und anschließend entfernen
 *
 * Verhalten:
 * - Unterstützt message als String ODER Array von Strings.
 * - Typklasse wird whitelisted (success|error|info|warning), Default: info.
 * - HTML-Output ist ge-escaped. ARIA-Rolle für bessere Zugänglichkeit.
 */

if (isset($_SESSION['message'])):
  $rawMessage = $_SESSION['message'];
  $rawType    = $_SESSION['type'] ?? 'info';

  // Erlaubte Typen (für CSS-Klassen)
  $allowedTypes = ['success', 'error', 'info', 'warning'];
  $type = in_array($rawType, $allowedTypes, true) ? $rawType : 'info';

  // In Array verwandeln, leere Einträge filtern
  $messages = is_array($rawMessage) ? $rawMessage : [$rawMessage];
  $messages = array_values(array_filter(array_map(static fn($m) => trim((string)$m), $messages), static fn($m) => $m !== ''));
  ?>
  <div class="msg <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" role="alert" aria-live="assertive" tabindex="-1">
    <?php if (count($messages) === 1): ?>
      <p><?php echo htmlspecialchars($messages[0], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif (count($messages) > 1): ?>
      <ul>
        <?php foreach ($messages as $m): ?>
          <li><?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <?php
  // Nach Ausgabe aufräumen (Flash-Verhalten)
  unset($_SESSION['message'], $_SESSION['type']);
endif;
