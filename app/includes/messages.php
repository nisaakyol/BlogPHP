<!-- Veränderungsdatum: 08.10.2024 
      Wenn in der Session eine Message gesetzt ist dann zeige sie mittels Echo und dem Type an. Danach Leere das Message und Type Attribut
-->

<?php if (isset($_SESSION['message'])): ?>
  <div class="msg <?php echo $_SESSION['type']; ?>">
    <li><?php echo $_SESSION['message']; ?></li>

    <?php
    unset($_SESSION['message']);
    unset($_SESSION['type']);

    ?>
  </div>

<?php endif; ?>