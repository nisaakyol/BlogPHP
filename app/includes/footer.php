<?php
/**
 * Datei: app/includes/footer.php
 * Zweck: Globale Fußzeile mit Kurztext, Quick-Links und Kontaktformular
 *
 * Hinweise:
 * - Quick-Links: reine Navigationslinks (semantisch als Liste ausgezeichnet).
 * - Kontaktformular: sendet per POST an app/helpers/Contact.php (bestehender Handler).
 * - Für Produktion: Optional CSRF-Token ergänzen.
 */
?>
<footer class="footer" role="contentinfo">
  <div class="footer-content">
    <!-- Info/Branding -->
    <section class="footer-section about" aria-labelledby="footer-about-heading">
      <h1 id="footer-about-heading" class="logo-text"><span>DH</span>BW Stuttgart</h1>
      <p>
        Die DHBW Stuttgart verfügt über 50 Jahre Erfahrung in qualitativ hochwertiger, dualer, praxisintegrierender
        Hochschulausbildung. Die Studierenden wechseln regelmäßig zwischen der Hochschule und dem Partnerunternehmen.
        So sammeln sie bereits während der Studienzeit wertvolle Berufserfahrung.
      </p>

      <div class="contact">
        <span><i class="fas fa-phone" aria-hidden="true"></i> <span class="sr-only">Telefon:</span> 0711 320-6600</span>
        <span><i class="fas fa-envelope" aria-hidden="true"></i> <span class="sr-only">E-Mail:</span> info@dhbw-stuttgart.de</span>
      </div>

      <div class="socials" aria-label="Soziale Medien">
        <a href="https://www.facebook.com/dhbwstuttgart" aria-label="Facebook (DHBW Stuttgart)"><i class="fab fa-facebook" aria-hidden="true"></i></a>
        <a href="https://www.instagram.com/dhbwstuttgart/" aria-label="Instagram (DHBW Stuttgart)"><i class="fab fa-instagram" aria-hidden="true"></i></a>
        <a href="https://de.linkedin.com/school/dhbw-stuttgart/" aria-label="LinkedIn (DHBW Stuttgart)"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
        <a href="https://www.youtube.com/user/dhbwstuttgart" aria-label="YouTube (DHBW Stuttgart)"><i class="fab fa-youtube" aria-hidden="true"></i></a>
      </div>
    </section>

    <!-- Quick Links -->
    <nav class="footer-section links" aria-labelledby="footer-links-heading">
      <h2 id="footer-links-heading">Quick Links</h2>
      <ul>
        <li><a href="#">Events</a></li>
        <li><a href="<?php echo BASE_URL . '/hardcode/team.php'; ?>">Team</a></li>
        <li><a href="#">Mentores</a></li>
        <li><a href="#">Gallery</a></li>
        <li><a href="<?php echo BASE_URL . '/hardcode/TermsandConditions.php'; ?>">Terms and Conditions</a></li>
      </ul>
    </nav>

    <!-- Kontaktformular -->
    <section class="footer-section contact-form" aria-labelledby="footer-contact-heading">
      <h2 id="footer-contact-heading">Contact us</h2>

      <form action="<?php echo BASE_URL . '/app/helpers/Contact.php'; ?>" method="post" novalidate>
        <!-- Optional (empfohlen): <input type="hidden" name="csrf_token" value="<?php /* echo $_SESSION['csrf_token'] ?? '' */ ?>"> -->

        <label for="email" class="sr-only">Your email address</label>
        <input
          type="email"
          name="Adresse"
          id="email"
          class="text-input contact-input"
          placeholder="Your email address..."
          required
        >

        <label for="message" class="sr-only">Your message</label>
        <textarea
          rows="4"
          name="message"
          id="message"
          class="text-input contact-input"
          placeholder="Your message..."
          required
        ></textarea>

        <button type="submit" class="btn btn-big contact-btn" aria-label="Send message">
          <i class="fas fa-envelope" aria-hidden="true"></i>
          Send
        </button>
      </form>
    </section>
  </div>
</footer>
