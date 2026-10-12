<?php
/**
 * Site root template: the editable Home page once setup has created it, otherwise the original home layout.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
if ( 'page' === get_option( 'show_on_front' ) && have_posts() ) {
    while ( have_posts() ) {
        the_post();
        echo '<div class="bsets-content">';
        the_content();
        echo '</div>';
    }
} else {
    echo '<div class="bsets-content">' . apply_filters( 'the_content', bsets_page_markup( 'home' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
get_footer();

