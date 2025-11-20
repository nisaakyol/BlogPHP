<?php
// Datei: app/Support/includes/header.php
?>

<style>
  /* ----------------------------------------------------------
     Header Layout
  ---------------------------------------------------------- */
  header[role="banner"] {
    background: #2e3a46;                 /* Blau */
    height: 66px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 25px;
    box-shadow: 0 10px 35px rgba(0,0,0,.15);
    position: relative;
    z-index: 2000;
    border-radius: 0 !important;         /* KEINE Rundung */
  }

  header[role="banner"] a,
  header[role="banner"] i {
    color: #efe7dd !important;
  }

  /* ----------------------------------------------------------
     Linke / rechte Navigation
  ---------------------------------------------------------- */
  .nav-left,
  .nav-right {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
  }

  .nav-left {
    gap: 2rem;
  }

  .nav-right {
    gap: 1.5rem;
    position: relative;
  }

  /* Alle Links im Header – OHNE Rundungen */
  .nav-left a,
  .nav-right > li > a {
    font-size: 1.05rem;
    padding: 8px 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 0;                    /* <<< keine Ecken */
    transition: background 0.25s ease;
  }

  .nav-left a:hover,
  .nav-right > li > a:hover {
    background: rgba(255,255,255,0.15);
  }

  /* ----------------------------------------------------------
     Dropdown – SAND & KEINE ECKEN
  ---------------------------------------------------------- */
  .nav-right li {
    position: relative;
  }

  .dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 4px);    /* direkt unter dem Button */
    right: 0;

    background: #efe7dd;      /* Sand-Farbe wie im Admin-Header */
    border-radius: 0;         /* <<< keine Rundung */
    list-style: none;
    min-width: 190px;
    padding: 6px 0;
    margin: 0;
    box-shadow: 0 10px 30px rgba(0,0,0,.18);
    z-index: 3000;
  }

  .dropdown li {
    margin: 0;
  }

  .dropdown li a {
    display: block;
    padding: 10px 18px;
    background: transparent !important;
    color: #2e3a46 !important;
    font-size: 0.98rem;
    border-radius: 0;          /* sicherheitshalber */
    transition: background 0.2s ease, color 0.2s ease;
    white-space: nowrap;
  }

  .dropdown li + li a {
    border-top: 1px solid rgba(0,0,0,.04);
  }

  .dropdown li a:hover {
    background: rgba(255,255,255,0.65);
  }

  /* KEIN :hover, Öffnen/Schließen macht das JS */

  /* Klickfläche etwas vergrößern */
  .nav-right > li > a {
    padding-bottom: 12px;
  }

  /* ----------------------------------------------------------
     Mobile
  ---------------------------------------------------------- */
  .menu-toggle {
    display: none;
    font-size: 1.7rem;
    cursor: pointer;
  }

  @media (max-width: 768px) {
    .menu-toggle {
      display: block;
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
    }

    header[role="banner"] {
      justify-content: center;
    }

    .nav-left {
      display: none; /* später evtl. Slide-Menü */
    }
  }
</style>

<header role="banner">

  <!-- Mobile Menu Icon -->
  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <!-- NAVIGATION LINKS (LEFT) -->
  <ul class="nav-left" aria-label="Hauptnavigation">
    <li><a href="<?php echo BASE_URL . '/public/index.php'; ?>">Home</a></li>
    <li><a href="<?php echo BASE_URL . '/public/resources/static/about.php'; ?>">About</a></li>
  </ul>

  <!-- USER AREA (RIGHT) -->
  <ul class="nav-right">
    <?php if (isset($_SESSION['id'])): ?>
      <li>
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user"></i>
          <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;"></i>
        </a>

        <ul class="dropdown" role="menu">
          <?php if (!empty($_SESSION['admin'])): ?>
            <li>
              <a href="<?php echo BASE_URL . '/public/admin/dashboard.php'; ?>">
                Dashboard
              </a>
            </li>
          <?php else: ?>
            <li>
              <a href="<?php echo BASE_URL . '/public/users/dashboard.php'; ?>">
                Dashboard
              </a>
            </li>
          <?php endif; ?>

          <li>
            <a href="<?php echo BASE_URL . '/public/logout.php'; ?>" class="logout">
              Logout
            </a>
          </li>
        </ul>
      </li>
    <?php else: ?>
      <li><a href="<?php echo BASE_URL . '/public/register.php'; ?>">Sign Up</a></li>
      <li><a href="<?php echo BASE_URL . '/public/login.php'; ?>">Login</a></li>
    <?php endif; ?>
  </ul>

</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const userItem = document.querySelector(".nav-right li");
    const dropdown = document.querySelector(".nav-right .dropdown");
    if (!userItem || !dropdown) return;

    let closeTimer = null;

    function openDropdown() {
        clearTimeout(closeTimer);
        dropdown.style.display = "block";
    }

    function scheduleClose() {
        closeTimer = setTimeout(() => {
            dropdown.style.display = "none";
        }, 350); // 350 ms „Gnadenzeit“
    }

    // Hover-Bereich: über dem Usernamen
    userItem.addEventListener("mouseenter", openDropdown);
    userItem.addEventListener("mouseleave", scheduleClose);

    // Wenn Maus im Dropdown ist → offen halten
    dropdown.addEventListener("mouseenter", openDropdown);
    dropdown.addEventListener("mouseleave", scheduleClose);

    // Extra: Klick zum Öffnen/Schließen (praktisch für Touchpads)
    const triggerLink = userItem.querySelector("a");
    if (triggerLink) {
        triggerLink.addEventListener("click", function (e) {
            e.preventDefault();
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                openDropdown();
            }
        });
    }
});
</script>
