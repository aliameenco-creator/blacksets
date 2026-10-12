<?php
/**
 * Single blog post template.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) :
    the_post();
    $thumb = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    ?>
    <article class="bsets-article">
      <header class="bsets-article-header">
        <div class="bsets-article-meta">
          <span class="eyebrow"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
          <h1 class="bsets-article-title"><?php the_title(); ?></h1>
        </div>
        <?php if ( $thumb ) : ?>
          <div class="bsets-article-featured">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
          </div>
        <?php endif; ?>
      </header>
      <div class="bsets-content bsets-prose">
        <?php the_content(); ?>
      </div>
      <footer class="bsets-article-footer">
        <div class="bsets-prose">
          <p><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="bsets-btn is-style-outline">← Back to News</a></p>
        </div>
      </footer>
    </article>
    <?php
endwhile;
get_footer();

