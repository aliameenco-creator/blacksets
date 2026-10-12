<?php
/**
 * Page content, header/footer parts and original URLs for Black Sets.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Original URL slugs for Black Sets. */
function bsets_routes() {
    return array( 'gallery', '3d-scans', 'projects', 'blog', 'contact', 'about-7', 'privacy-policy', 'terms-conditions' );
}

function bsets_body_class() {
    $classes = array();
    if ( is_front_page() || is_page( 'home' ) ) {
        $classes[] = 'has-hero';
        $classes[] = 'page-home';
    }
    return $classes;
}

/** Media Library URL for an imported theme image/video key, or ''. */
function bsets_media_url( $key ) {
    $ids = bsets_media_ids();
    return isset( $ids[ $key ] ) ? (string) wp_get_attachment_url( $ids[ $key ] ) : '';
}

/**
 * Resolve generated markup's tokens: image blocks get Media Library attachment IDs,
 * and /assets/img/... and /assets/video/... point to Media Library copy or theme copy.
 */
function bsets_resolve_blocks( $markup ) {
    $ids = bsets_media_ids();
    $markup = preg_replace_callback( '~<!-- wp:image (\{[^}]*\}) -->(.*?)<!-- /wp:image -->~s', function ( $m ) use ( $ids ) {
        if ( ! preg_match( '~/assets/img/([a-z0-9]+)-\d+\.webp~', $m[2], $k ) || empty( $ids[ $k[1] ] ) ) { return $m[0]; }
        $id = (int) $ids[ $k[1] ];
        return '<!-- wp:image ' . str_replace( '"id":0', '"id":' . $id, $m[1] ) . ' -->' . str_replace( 'wp-image-0', 'wp-image-' . $id, $m[2] ) . '<!-- /wp:image -->';
    }, $markup );
    $markup = preg_replace_callback( '~/assets/img/([a-z0-9]+)-(\d+)\.webp~', function ( $m ) {
        $url = bsets_media_url( $m[1] );
        return $url ? $url : get_theme_file_uri( 'assets/img/' . $m[1] . '-' . $m[2] . '.webp' );
    }, $markup );
    return preg_replace_callback( '~/assets/video/([a-z0-9-]+)\.mp4~', function ( $m ) {
        $url = bsets_media_url( $m[1] );
        return $url ? $url : get_theme_file_uri( 'assets/video/' . $m[1] . '.mp4' );
    }, $markup );
}

/** Block markup of an original page (tokens resolved), or ''. */
function bsets_page_markup( $slug ) {
    if ( ! preg_match( '~^[a-z0-9-]+$~D', $slug ) ) { return ''; }
    $file = get_theme_file_path( 'parts/pages/' . $slug . '.html' );
    return is_readable( $file ) ? bsets_resolve_blocks( (string) file_get_contents( $file ) ) : '';
}

/** Root-relative links become full links. */
function bsets_links( $html ) {
    return preg_replace_callback( '~href="(/(?!/)[^"<>]*)"~', function ( $m ) {
        $path = preg_match( '~^/[a-z0-9-]+$~D', $m[1] ) ? user_trailingslashit( $m[1] ) : $m[1];
        return 'href="' . esc_url( home_url( $path ) ) . '"';
    }, $html );
}

/** Page heading band for pages without their own hero. */
function bsets_page_head( $title, $intro = '' ) {
    echo '<section class="bsets-page-hero bsets-hero-plain"><div class="bsets-hero-inner"><h1 class="bsets-title">' . esc_html( $title ) . '</h1>' . ( $intro ? '<p class="lead">' . esc_html( $intro ) . '</p>' : '' ) . '</div></section>';
}

/** Request path relative to site root. */
function bsets_request_path() {
    $path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
    $base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
    if ( $base && str_starts_with( $path, $base ) ) { $path = substr( $path, strlen( $base ) ); }
    return trim( rawurldecode( $path ), '/' );
}

/** Original URL fallback: handles route before setup creates the page. */
function bsets_virtual_route() {
    $path = bsets_request_path();
    if ( ! in_array( $path, bsets_routes(), true ) || is_preview() ) { return ''; }
    if ( is_404() ) { return $path; }
    if ( is_page() && 'publish' !== get_post_status( get_queried_object_id() ) ) { return $path; }
    return '';
}

/** Redirect duplicate/legacy paths */
add_action( 'template_redirect', function () {
    $path = bsets_request_path();
    if ( in_array( $path, array( 'copy-of-home-1', 'blacksets', 'fullscreen-page' ), true ) ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
    if ( 'faq' === $path ) {
        wp_safe_redirect( home_url( '/about-7' ), 301 );
        exit;
    }
    if ( str_starts_with( $path, 'blacksets/' ) ) {
        $sub = substr( $path, strlen( 'blacksets/' ) );
        wp_safe_redirect( home_url( '/' . $sub ), 301 );
        exit;
    }
    $route = bsets_virtual_route();
    if ( ! $route ) { return; }
    status_header( 200 );
    get_header();
    echo '<div class="bsets-content">' . apply_filters( 'the_content', bsets_page_markup( $route ) ) . '</div>';
    get_footer();
    exit;
} );

/** Render main menu: uses registered wp_nav_menu or fallback list. */
function bsets_render_menu() {
    if ( has_nav_menu( 'primary' ) ) {
        wp_nav_menu( array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'menu',
            'fallback_cb'    => 'bsets_fallback_menu',
            'depth'          => 2,
        ) );
        return;
    }
    bsets_fallback_menu();
}

function bsets_fallback_menu() {
    $items = array(
        'Home'     => home_url( '/' ),
        'Gallery'  => home_url( '/gallery' ),
        '3D Scans' => home_url( '/3d-scans' ),
        'Projects' => home_url( '/projects' ),
        'News'     => home_url( '/blog' ),
        'Contact'  => home_url( '/contact' ),
    );
    $current_path = bsets_request_path();
    echo '<ul class="menu">';
    foreach ( $items as $label => $url ) {
        $slug = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
        $is_curr = ( '' === $current_path && home_url( '/' ) === $url ) || ( $current_path === $slug );
        $cls = $is_curr ? ' current-menu-item' : '';
        echo '<li class="menu-item' . esc_attr( $cls ) . '"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
    }
    echo '</ul>';
}

