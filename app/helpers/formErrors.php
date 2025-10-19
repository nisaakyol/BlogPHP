<?php
/**
 * Robuste Fehlerliste für Formulare.
 * Nutzt bevorzugt $errors aus dem aktuellen Scope,
 * fällt zurück auf $_SESSION['form_errors'].
 */
$__errs = $errors ?? ($_SESSION['form_errors'] ?? []);
if (!is_array($__errs)) {
    $__errs = []; // safety
}
if (count($__errs) > 0): ?>
    <div class="msg error">
        <ul>
            <?php foreach ($__errs as $err): ?>
                <li><?php echo htmlspecialchars((string)$err, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
