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

