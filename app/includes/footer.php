<!-- Veränderungsdatum: 08.10.2024 
      Footer für alle Php-Seiten mit Verlinkungen auf weitere Seiten. Überwiegend Hardcode
-->

<!-- Fußzeile mit Verlinkungen-->
<div class="footer">
  <div class="footer-content">

    <div class="footer-section about">
      <h1 class="logo-text"><span>DH</span>BW Stuttgart</h1>
      <p>
        Die DHBW Stuttgart verfügt über 50 Jahre Erfahrung in qualitativ hochwertiger, dualer, praxisintegrierender
        Hochschulausbildung.
        Die Studierenden wechseln regelmäßig zwischen der Hochschule und dem Partnerunternehmen.
        So sammeln sie bereits während der Studienzeit wertvolle Berufserfahrung.
      </p>
      <div class="contact">
        <span><i class="fas fa-phone"></i> &nbsp; 0711 320-6600</span>
        <span><i class="fas fa-envelope"></i> &nbsp; info@dhbw-stuttgart.de</span>
      </div>
      <div class="socials">
        <a href="https://www.facebook.com/dhbwstuttgart"><i class="fab fa-facebook"></i></a>
        <a href="https://www.instagram.com/dhbwstuttgart/"><i class="fab fa-instagram"></i></a>
        <a href="https://de.linkedin.com/school/dhbw-stuttgart/"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.youtube.com/user/dhbwstuttgart"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <div class="footer-section links">
      <h2>Quick Links</h2>
      <br>
      <ul>
        <a href="#">
          <li>Events</li>
        </a>
        <a href="<?php echo BASE_URL . '/hardcode/team.php' ?>">
          <li>Team</li>
        </a>
        <a href="#">
          <li>Mentores</li>
        </a>
        <a href="#">
          <li>Gallery</li>
        </a>
        <a href="<?php echo BASE_URL . '/hardcode/TermsandConditions.php' ?>">
          <li>Terms and Conditions</li>
        </a>
      </ul>
    </div>

    <!-- Kontakt Formular -->
    <div class="footer-section contact-form">
      <h2>Contact us</h2>
      <br>

      <form action="<?php echo BASE_URL . '/app/helpers/Contact.php' ?>" method="post">

        <input type="email" name="Adresse" class="text-input contact-input" id="email"
          placeholder="Your email address...">

        <textarea rows="4" name="message" class="text-input contact-input" id="message"
          placeholder="Your message..."></textarea>

        <button type="submit" class="btn btn-big contact-btn">
          <i class="fas fa-envelope"></i>
          Send
        </button>
      </form>
    </div>

  </div>

</div>
<!-- // footer -->