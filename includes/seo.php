<?php
/**
 * SEO preservation for Black Sets: titles, meta descriptions, Open Graph and Twitter Card tags.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bsets_seo_data() {
    static $seo = null;
    if ( null === $seo ) {
        $file = get_theme_file_path( 'parts/seo.json' );
        $seo = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array();
    }
    return is_array( $seo ) ? $seo : array();
}

function bsets_current_seo_key() {
    if ( is_front_page() ) { return 'home'; }
    $path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
    return $path ?: 'home';
}

add_filter( 'pre_get_document_title', function ( $title ) {
    $data = bsets_seo_data();
    $key = bsets_current_seo_key();
    if ( ! empty( $data[ $key ]['title'] ) ) { return $data[ $key ]['title']; }
    return $title;
}, 20 );

add_action( 'wp_head', function () {
    $data = bsets_seo_data();
    $key = bsets_current_seo_key();
    $item = $data[ $key ] ?? array();
    $title = $item['title'] ?? get_bloginfo( 'name' );
    $desc = $item['desc'] ?? get_bloginfo( 'description' );
    $url = esc_url( home_url( '/' . ( 'home' === $key ? '' : $key ) ) );
    $image = esc_url( get_theme_file_uri( 'assets/bsets-logo.png' ) );

    echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<link rel="canonical" href="' . $url . '">' . "\n";
    echo '<meta property="og:locale" content="en_GB">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta property="og:url" content="' . $url . '">' . "\n";
    echo '<meta property="og:site_name" content="Black Sets">' . "\n";
    echo '<meta property="og:image" content="' . $image . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:site" content="@black_sets">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . $image . '">' . "\n";
}, 5 );

