<?php
/**
 * Black Sets Theme Footer.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
</main>
<footer class="site-footer" id="footer">
  <div class="footer-inner">
    <div class="footer-col footer-brand">
      <a class="footer-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <img src="<?php echo esc_url( get_theme_file_uri( 'assets/bsets-logo.png' ) ); ?>" alt="Black Sets" class="footer-logo" width="150" height="45">
      </a>
      <p class="footer-desc">Bespoke film and television set construction, carpentry fabrication, 3D laser scanning, and virtual production environments at Black Hangar Studios.</p>
      <ul class="bsets-socials footer-socials">
        <li><a href="https://www.instagram.com/blacksetsfilm" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3.8" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="17.2" cy="6.8" r="1.1"/></svg></a></li>
        <li><a href="https://www.facebook.com/BlackSetsFilm" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M13.5 21v-7.6h2.6l.4-3h-3V8.5c0-.9.3-1.5 1.6-1.5h1.6V4.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H7.9v3h2.6V21z"/></svg></a></li>
        <li><a href="https://twitter.com/black_sets" target="_blank" rel="noopener" aria-label="X (Twitter)"><svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a></li>
        <li><a href="https://www.linkedin.com/company/black-sets-film-and-tv/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg></a></li>
      </ul>
    </div>

    <div class="footer-col footer-nav">
      <h4 class="footer-heading">Navigation</h4>
      <ul class="footer-links">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
        <li><a href="<?php echo esc_url( home_url( '/gallery' ) ); ?>">Gallery</a></li>
        <li><a href="<?php echo esc_url( home_url( '/3d-scans' ) ); ?>">3D Scans</a></li>
        <li><a href="<?php echo esc_url( home_url( '/projects' ) ); ?>">Projects</a></li>
        <li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">News</a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></li>
      </ul>
    </div>

    <div class="footer-col footer-contact">
      <h4 class="footer-heading">Studio &amp; Workshop</h4>
      <p>Black Hangar Studios<br>Lasham Airfield, Alton<br>GU34 5SR, United Kingdom</p>
      <p class="footer-contact-links">
        <a href="tel:+441256384235">+44 (0) 1256 384235</a><br>
        <a href="mailto:info@blacklab.com">info@blacklab.com</a><br>
        <a href="mailto:blackvfx@blackhangarstudios.com">blackvfx@blackhangarstudios.com</a>
      </p>
    </div>

    <div class="footer-col footer-subscribe">
      <h4 class="footer-heading">Join Our Mailing List</h4>
      <p>Subscribe for updates on our latest studio builds and virtual production work.</p>
      <?php echo bsets_render_form( 'subscribe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <p class="copyright">Copyright &copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Black Sets. All rights reserved.</p>
      <ul class="footer-legal">
        <li><a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>">Privacy Policy</a></li>
        <li><a href="<?php echo esc_url( home_url( '/about-7' ) ); ?>">FAQ</a></li>
        <li><a href="<?php echo esc_url( home_url( '/terms-conditions' ) ); ?>">Terms &amp; Conditions</a></li>
      </ul>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

