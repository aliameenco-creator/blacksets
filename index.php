<?php
/**
 * Main archive / blog index template.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<section class="bsets-page-hero bsets-hero-plain">
  <div class="bsets-hero-inner">
    <span class="eyebrow">NEWS &amp; UPDATES</span>
    <h1 class="bsets-title">Production News</h1>
    <p class="lead">Latest announcements and behind the scenes news from Black Sets.</p>
  </div>
</section>
<section class="bsets-section bsets-news-section">
  <div class="bsets-content">
    <?php if ( have_posts() ) : ?>
      <div class="bsets-post-grid">
        <?php while ( have_posts() ) : the_post();
          $thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
        ?>
          <article class="bsets-post-card">
            <?php if ( $thumb ) : ?>
              <a href="<?php the_permalink(); ?>" class="bsets-post-thumb"><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"/></a>
            <?php endif; ?>
            <div class="bsets-post-body">
              <span class="eyebrow"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
              <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
              <a href="<?php the_permalink(); ?>" class="bsets-readmore">Read article →</a>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    <?php else : ?>
      <div class="bsets-empty-notice">
        <h3>Check Back Soon</h3>
        <p>Once news and production updates are published, you will see them here.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
get_footer();

