<header role="banner" class="admin-header">

  <a class="logo" href="<?php echo BASE_URL . '/public/index.php'; ?>" aria-label="Startseite">
    <h1 class="logo-text">Startseite</h1>
  </a>

  <i class="fa fa-bars menu-toggle" aria-hidden="true"></i>

  <ul class="nav admin-nav" role="navigation" aria-label="Admin Navigation">
    <?php if (isset($_SESSION['id'])): ?>
      <li class="nav-user">
        <a href="#" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-user" aria-hidden="true"></i>
          <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
          <i class="fa fa-chevron-down" style="font-size:.8em;" aria-hidden="true"></i>
        </a>

        <ul class="dropdown admin-dropdown" role="menu">
          <li>
            <a href="<?php echo BASE_URL . '/public/logout.php'; ?>" class="logout" role="menuitem">
              Logout
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>
</header>

<style>
/* ---------------------------------------------
   Admin Header Style
---------------------------------------------- */
.admin-header {
  background: #2e3a46;
  height: 66px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 25px;
  box-shadow: 0 10px 30px rgba(0,0,0,.25);
  position: relative;
  z-index: 2000;
}

.admin-header .logo-text,
.admin-header a,
.admin-header i {
  color: #efe7dd !important;
}

.admin-nav {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.admin-nav li {
  position: relative;
}

.admin-nav > li > a {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 12px;
  transition: background .25s ease;
}

.admin-nav > li > a:hover {
  background: rgba(255,255,255,.15);
}

/* ---------------------------------------------
   Dropdown – SAND, aber OHNE ECKEN
---------------------------------------------- */
.admin-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 6px);
  right: 0;

  background: #efe7dd;
  border-radius: 0;        /* <<< KEINE RUNDEN ECKEN */
  min-width: 180px;
  padding: 6px 0;
  box-shadow: 0 12px 32px rgba(0,0,0,.22);
  list-style: none;
  margin: 0;
  z-index: 3000;
}

.admin-dropdown li a {
  display: block;
  padding: 12px 18px;
  color: #2e3a46 !important;
  background: transparent;
  font-size: .95rem;
  transition: background .2s ease;
}

.admin-dropdown li + li a {
  border-top: 1px solid rgba(0,0,0,.05);
}

.admin-dropdown li a:hover {
  background: rgba(255,255,255,.65);
}

/* Dropdown bleibt offen beim Hineinfahren */
.admin-nav li:hover > .admin-dropdown,
.admin-nav li:focus-within > .admin-dropdown,
.admin-dropdown:hover {
  display: block;
}

/* Größere Klickfläche für Stabilität */
.nav-user > a {
  padding-bottom: 14px;
}

/* Mobile */
.menu-toggle {
  display: none;
}

@media (max-width: 768px) {
  .menu-toggle {
    display: block;
    font-size: 1.7rem;
    cursor: pointer;
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
  }

  .nav-left {
    display: none;
  }
}
</style>
