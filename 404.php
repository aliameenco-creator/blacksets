<?php
/**
 * 404 Not Found template.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<section class="bsets-page-hero bsets-hero-plain">
  <div class="bsets-hero-inner">
    <span class="eyebrow">404 ERROR</span>
    <h1 class="bsets-title">Page Not Found</h1>
    <p class="lead">The page you were looking for does not exist or has been moved.</p>
    <div class="wp-block-buttons" style="margin-top:28px">
      <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return Home</a></div>
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact Us</a></div>
    </div>
  </div>
</section>
<?php
get_footer();

