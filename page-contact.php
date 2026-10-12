<?php
/**
 * Template Name: Contact
 * Direct contact template: works even before setup creates the page in the database.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        echo '<div class="bsets-content">';
        the_content();
        echo '</div>';
    }
} else {
    echo '<div class="bsets-content">' . apply_filters( 'the_content', bsets_page_markup( 'contact' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
get_footer();

