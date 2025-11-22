<?php
// Zweck: Globale Fußzeile mit Kurztext, Quick-Links und Kontaktformular
?>

<style>
  /* --- Footer Grunddesign --- */
  .footer {
    background: #2e3a46;              /* Dunkles Blau */
    color: #d3d3d3;
    width: 100%;
    margin: 0;
    padding: 0;
    border: none;
  }

  /* Beige Fläche unter dem Footer entfernen */
  body {
    background: #fdfefe !important;   /* Hell ohne Beige */
  }

  .footer-content {
    display: flex;
    padding: 40px 60px;
    justify-content: space-between;
  }

  .footer-section {
    flex: 1;
    margin-right: 40px;
  }

  .footer h1,
  .footer h2 {
    color: #ffffff;
    margin-bottom: 15px;
  }

  /* Kontakt Icons */
  .socials a {
    border: 1px solid #cfd2d4;
    padding: 8px;
    margin-right: 4px;
    border-radius: 6px;
    display: inline-block;
    color: #fff;
  }

  .socials a:hover {
    background: #1d252c;
  }

  /* Footer Inputs */
  .footer .contact-input {
    background: #1f2933;
    color: #e5e7eb;
    padding: 12px;
    width: 100%;
    border-radius: 6px;
    border: none;
    margin-bottom: 12px;
  }

  .footer .contact-btn {
    background: #ffffff;
    color: #2e3a46;
    border: none;
    border-radius: 999px;

    padding: 2px 10px;
    font-size: 0.65rem;
    gap: 3px;

    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;

    position: relative;
    top: -12px;             /* <<< HEBT ihn unter das Textfeld */
    margin-bottom: 5px !important;   /* <<< weniger Abstand unten */
}
  .footer .contact-btn:hover {
    background: #dce1e6;
  }

  /* Footer Bottom Strip */
  .footer-bottom {
    background: #1f2933;
    color: #cfd5dd;
    padding: 15px 0;
    text-align: center;
    border: none;
    margin-top: 20px;
  }

  /* Mobile Layout */
  @media (max-width: 900px) {
    .footer-content {
      flex-direction: column;
      gap: 40px;
    }
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<footer class="footer" role="contentinfo">
  <div class="footer-content">

    <!-- Info/Branding -->
    <section class="footer-section about" aria-labelledby="footer-about-heading">
      <h1 id="footer-about-heading" class="logo-text">Contact Information</h1>

      <div class="contact">
        <span><i class="fas fa-phone" aria-hidden="true"></i> 0711 00000000</span><br>
        <span><i class="fas fa-envelope" aria-hidden="true"></i> travel-blog@blog.de</span>
      </div>

      <div class="socials" aria-label="Soziale Medien">
        <a href="https://www.facebook.com/dhbwstuttgart"><i class="fab fa-facebook"></i></a>
        <a href="https://www.instagram.com/dhbwstuttgart/"><i class="fab fa-instagram"></i></a>
        <a href="https://de.linkedin.com/school/dhbw-stuttgart/"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.youtube.com/user/dhbwstuttgart"><i class="fab fa-youtube"></i></a>
      </div>
    </section>

    <!-- Quick Links -->
    <nav class="footer-section links" aria-labelledby="footer-links-heading">
      <h2 id="footer-links-heading">Quick Links</h2>
      <ul>
        <li><a href="<?php echo BASE_URL . '/public/resources/static/team.php'; ?>">Team</a></li>
        <li><a href="<?php echo BASE_URL . '/public/resources/static/termsOfUse.php'; ?>">Terms of Use</a></li>
      </ul>
    </nav>

    <!-- Kontaktformular -->
    <section class="footer-section contact-form" aria-labelledby="footer-contact-heading">
      <h2 id="footer-contact-heading">Contact us</h2>

      <form action="<?php echo BASE_URL . '/app/Support/helpers/Contact.php'; ?>" method="post" novalidate>

        <input
          type="email"
          name="Adresse"
          class="text-input contact-input"
          placeholder="Your email address..."
          required
        >

        <textarea
          rows="4"
          name="message"
          class="text-input contact-input"
          placeholder="Your message..."
          required
        ></textarea>

        <button type="submit" class="btn btn-big contact-btn">
          <i class="fas fa-envelope"></i> Send
        </button>

      </form>
    </section>

  </div>

  <div class="footer-bottom">
    © 2025 Travel Blog — All rights reserved.
  </div>
</footer>
