<?php
/**
 * WordPress pages: original pages carry their own hero; custom user pages get a clean hero band.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) :
    the_post();
    $has_hero = str_contains( (string) get_the_content(), 'bsets-page-hero' );
    if ( ! $has_hero ) { bsets_page_head( get_the_title() ); }
    echo '<div class="bsets-content' . ( $has_hero ? '' : ' bsets-prose' ) . '">';
    the_content();
    echo '</div>';
endwhile;
get_footer();

