<?php
/**
 * Editable WordPress pages for every original page (parts/pages.json + parts/pages/<slug>.html) and main menu.
 * The setup button creates them once, at their original addresses, after images and videos are in the Media Library.
 * Existing published pages are never overwritten; pages deleted by an editor are not re-created.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bsets_pages_data() {
    static $pages = null;
    if ( null === $pages ) {
        $file = get_theme_file_path( 'parts/pages.json' );
        $pages = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array();
    }
    return $pages;
}

function bsets_page_by_slug( $slug ) {
    $ids = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ), 'numberposts' => 1, 'fields' => 'ids' ) );
    if ( ! $ids ) { $ids = get_posts( array( 'post_type' => 'page', 'name' => $slug . '__trashed', 'post_status' => 'trash', 'numberposts' => 1, 'fields' => 'ids' ) ); }
    return $ids ? (int) $ids[0] : 0;
}

function bsets_sample_privacy_page() {
    $id = (int) get_option( 'wp_page_for_privacy_policy' );
    if ( ! $id || 'draft' !== get_post_status( $id ) ) { return 0; }
    return str_contains( (string) get_post_field( 'post_content', $id ), 'Suggested text' ) ? $id : 0;
}

function bsets_pages_pending() {
    $pending = array();
    foreach ( bsets_pages_data() as $page ) {
        if ( get_option( 'bsets_page_' . $page['slug'] ) ) { continue; }
        $existing = bsets_page_by_slug( $page['slug'] );
        if ( $existing && ! ( ! empty( $page['privacy'] ) && $existing === bsets_sample_privacy_page() ) ) { continue; }
        $pending[] = $page;
    }
    if ( ! get_option( 'bsets_menu_created' ) ) { $pending[] = array( 'slug' => '_menu' ); }
    return $pending;
}

function bsets_pages_seed() {
    if ( ! current_user_can( 'edit_pages' ) ) { return 0; }
    $created = 0;
    foreach ( bsets_pages_pending() as $page ) {
        if ( '_menu' === $page['slug'] ) { continue; }
        $postarr = array(
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => bsets_page_markup( $page['slug'] ),
        );
        $sample = ! empty( $page['privacy'] ) ? bsets_sample_privacy_page() : 0;
        if ( $sample ) { $postarr['ID'] = $sample; }
        $id = $sample ? wp_update_post( wp_slash( $postarr ), true ) : wp_insert_post( wp_slash( $postarr ), true );
        if ( is_wp_error( $id ) ) { continue; }
        update_option( 'bsets_page_' . $page['slug'], (int) $id, false );
        if ( ! empty( $page['seo_title'] ) ) { update_post_meta( $id, '_yoast_wpseo_title', $page['seo_title'] ); }
        if ( ! empty( $page['front'] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', (int) $id );
        }
        if ( ! empty( $page['privacy'] ) ) { update_option( 'wp_page_for_privacy_policy', (int) $id ); }
        bsets_mark_seeded( $id );
        ++$created;
    }
    bsets_menu_seed();
    bsets_trash_samples();
    return $created;
}

function bsets_content_version() {
    $file = get_theme_file_path( 'parts/content-version.txt' );
    return is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : '0.1.0';
}

function bsets_mark_seeded( $id ) {
    update_post_meta( $id, '_bsets_seed_version', bsets_content_version() );
    update_post_meta( $id, '_bsets_seed_hash', md5( (string) get_post_field( 'post_content', $id ) ) );
}

function bsets_menu_seed() {
    if ( get_option( 'bsets_menu_created' ) ) { return; }
    $menu_name = 'Main Menu';
    $menu_id = wp_create_nav_menu( $menu_name );
    if ( is_wp_error( $menu_id ) ) {
        $term = get_term_by( 'name', $menu_name, 'nav_menu' );
        $menu_id = $term ? (int) $term->term_id : 0;
    }
    if ( ! $menu_id ) { return; }

    $menu_items = array(
        array( 'title' => 'Home', 'slug' => 'home', 'url' => home_url( '/' ) ),
        array( 'title' => 'Gallery', 'slug' => 'gallery', 'url' => home_url( '/gallery' ) ),
        array( 'title' => '3D Scans', 'slug' => '3d-scans', 'url' => home_url( '/3d-scans' ) ),
        array( 'title' => 'Projects', 'slug' => 'projects', 'url' => home_url( '/projects' ) ),
        array( 'title' => 'News', 'slug' => 'blog', 'url' => home_url( '/blog' ) ),
        array( 'title' => 'Contact', 'slug' => 'contact', 'url' => home_url( '/contact' ) ),
    );

    foreach ( $menu_items as $order => $item ) {
        $page_id = (int) get_option( 'bsets_page_' . $item['slug'] );
        if ( $page_id ) {
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'     => $item['title'],
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $page_id,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-position'  => $order + 1,
            ) );
        } else {
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'    => $item['title'],
                'menu-item-url'      => $item['url'],
                'menu-item-type'     => 'custom',
                'menu-item-status'   => 'publish',
                'menu-item-position' => $order + 1,
            ) );
        }
    }
    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $locations['primary'] = $menu_id;
    set_theme_mod( 'nav_menu_locations', $locations );
    update_option( 'bsets_menu_created', (int) $menu_id, false );
}

function bsets_trash_samples() {
    $hello = get_posts( array( 'post_type' => 'post', 'name' => 'hello-world', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => 1 ) );
    if ( $hello ) { wp_trash_post( $hello[0] ); }
    $sample = get_posts( array( 'post_type' => 'page', 'name' => 'sample-page', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => 1 ) );
    if ( $sample ) { wp_trash_post( $sample[0] ); }
}


/**
 * Pages created by setup whose design is older than the theme's: [slug => [id, title, state]], state "unchanged"
 * (nobody edited it since setup) or "edited".
 */
function bsets_pages_outdated() {
    $out = array();
    foreach ( bsets_pages_data() as $page ) {
        $id = (int) get_option( 'bsets_page_' . $page['slug'] );
        if ( ! $id || 'publish' !== get_post_status( $id ) || get_post_meta( $id, '_bsets_seed_version', true ) === bsets_content_version() ) { continue; }
        $hash = get_post_meta( $id, '_bsets_seed_hash', true );
        $unchanged = $hash && md5( (string) get_post_field( 'post_content', $id ) ) === $hash;
        $out[ $page['slug'] ] = array( $id, $page['title'], $unchanged ? 'unchanged' : 'edited' );
    }
    return $out;
}

/** Replace a page's content with the theme's current design (WordPress keeps the previous version as a revision). */
function bsets_page_refresh( $slug ) {
    $id = (int) get_option( 'bsets_page_' . $slug );
    if ( ! $id || ! current_user_can( 'edit_page', $id ) ) { return false; }
    $result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => bsets_page_markup( $slug ) ) ), true );
    if ( is_wp_error( $result ) ) { return false; }
    bsets_mark_seeded( $id );
    return true;
}

/** Appearance → Black Sets Pages: update pages to a newer theme design, page by page. */
add_action( 'admin_menu', function () {
    add_theme_page( 'Black Sets Pages', 'Black Sets Pages', 'edit_pages', 'bsets-pages', 'bsets_pages_screen' );
} );

function bsets_pages_screen() {
    if ( ! current_user_can( 'edit_pages' ) ) { return; }
    echo '<div class="wrap"><h1>Black Sets Pages</h1>';
    if ( isset( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        echo '<div class="notice notice-success"><p>' . (int) $_GET['updated'] . ' page(s) updated to the new design. The previous version of each is saved under Revisions in the page editor.</p></div>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }
    if ( bsets_media_pending() ) {
        echo '<p>This theme version has new images. Add them to the Media Library first.</p><p><a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=bsets-setup' ) ) . '">Open Black Sets Setup</a></p></div>';
        return;
    }
    $outdated = bsets_pages_outdated();
    if ( ! $outdated ) { echo '<p>All pages created by the theme are up to date with version ' . esc_html( bsets_content_version() ) . '.</p></div>'; return; }
    echo '<p>The theme has an updated design for these pages. Pages nobody has edited are ticked. Updating a page you edited replaces your changes (you can restore them from the page\'s Revisions).</p>';
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="bsets_pages_refresh">';
    wp_nonce_field( 'bsets_pages_refresh' );
    echo '<table class="widefat striped" style="max-width:720px"><thead><tr><th style="width:40px"></th><th>Page</th><th>Status</th></tr></thead><tbody>';
    foreach ( $outdated as $slug => $row ) {
        echo '<tr><td><input type="checkbox" name="pages[]" value="' . esc_attr( $slug ) . '"' . checked( 'unchanged' === $row[2], true, false ) . '></td><td><a href="' . esc_url( get_edit_post_link( $row[0] ) ) . '">' . esc_html( $row[1] ) . '</a></td><td>' . ( 'unchanged' === $row[2] ? 'Not edited since setup' : '<strong>Edited</strong> — your changes would be replaced' ) . '</td></tr>';
    }
    echo '</tbody></table><p><button class="button button-primary" type="submit">Update selected pages</button></p></form></div>';
}

add_action( 'admin_post_bsets_pages_refresh', function () {
    if ( ! current_user_can( 'edit_pages' ) ) { wp_die( 'Not allowed.' ); }
    check_admin_referer( 'bsets_pages_refresh' );
    $slugs = array_map( 'sanitize_key', (array) wp_unslash( $_POST['pages'] ?? array() ) );
    $outdated = bsets_pages_outdated();
    $done = 0;
    foreach ( $slugs as $slug ) {
        if ( isset( $outdated[ $slug ] ) && bsets_page_refresh( $slug ) ) { ++$done; }
    }
    wp_safe_redirect( admin_url( 'themes.php?page=bsets-pages&updated=' . $done ) );
    exit;
} );

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'edit_pages' ) || ( isset( $_GET['page'] ) && 'bsets-pages' === $_GET['page'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( bsets_pages_pending() || ! bsets_pages_outdated() ) { return; }
    echo '<div class="notice notice-info"><p><strong>Black Sets:</strong> an updated design is available for your pages. <a class="button" href="' . esc_url( admin_url( 'themes.php?page=bsets-pages' ) ) . '">Review and update pages</a></p></div>';
} );
