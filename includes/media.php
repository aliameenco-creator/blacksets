<?php
/**
 * Media Library: one-click import of every image and video the theme shows.
 * Once imported, the theme serves those assets from the Media Library with editable alt text and srcset.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bsets_media_manifest() {
    static $manifest = null;
    if ( null === $manifest ) {
        $file = get_theme_file_path( 'parts/media.json' );
        $manifest = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array();
    }
    return $manifest;
}

/** Imported images: manifest key => attachment ID */
function bsets_media_ids() {
    static $ids = null;
    if ( null !== $ids ) { return $ids; }
    $ids = array_filter( array_map( 'intval', (array) get_option( 'bsets_media_ids', array() ) ) );
    if ( $ids ) {
        _prime_post_caches( array_values( $ids ), false, true );
        $ids = array_filter( $ids, function ( $id ) { return 'attachment' === get_post_type( $id ); } );
    }
    return $ids;
}

function bsets_media_pending() {
    $ids = bsets_media_ids();
    $failed = (array) get_option( 'bsets_media_failed', array() );
    return array_values( array_filter( bsets_media_manifest(), function ( $m ) use ( $ids, $failed ) {
        return empty( $ids[ $m['key'] ] ) && ! isset( $failed[ $m['key'] ] );
    } ) );
}

function bsets_media_import_url() {
    return add_query_arg( array( 'action' => 'bsets_media_import', '_wpnonce' => wp_create_nonce( 'bsets_media_import' ) ), admin_url( 'admin-post.php' ) );
}

add_action( 'admin_post_bsets_media_import', function () {
    if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) { wp_die( 'Not allowed.' ); }
    check_admin_referer( 'bsets_media_import' );
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $ids = (array) get_option( 'bsets_media_ids', array() );
    $failed = (array) get_option( 'bsets_media_failed', array() );
    $start = microtime( true );
    $imported = 0;

    foreach ( bsets_media_pending() as $m ) {
        $key = $m['key'];
        $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_bsets_media_key', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) );
        if ( $existing ) {
            $ids[ $key ] = (int) $existing[0];
            continue;
        }
        if ( $imported >= 6 || ( $imported && 'video' === ( $m['type'] ?? '' ) ) || microtime( true ) - $start > 18 ) {
            break;
        }
        $source = get_theme_file_path( $m['file'] );
        $ext = strtolower( pathinfo( $m['file'], PATHINFO_EXTENSION ) );
        $tmp = wp_tempnam( $m['name'] . '.' . $ext );
        if ( ! is_readable( $source ) || ! $tmp || ! copy( $source, $tmp ) ) {
            $failed[ $key ] = 'Bundled file missing: ' . $m['file'];
            continue;
        }
        $id = media_handle_sideload( array( 'name' => $m['name'] . '.' . $ext, 'tmp_name' => $tmp ), 0, $m['title'] );
        if ( is_wp_error( $id ) ) {
            wp_delete_file( $tmp );
            $failed[ $key ] = $id->get_error_message();
            continue;
        }
        if ( 'video' !== ( $m['type'] ?? '' ) ) {
            update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $m['alt'] ) );
        }
        update_post_meta( $id, '_bsets_media_key', $key );
        $ids[ $key ] = (int) $id;
        ++$imported;
    }

    update_option( 'bsets_media_ids', $ids, false );
    update_option( 'bsets_media_failed', $failed, false );

    $left = count( array_filter( bsets_media_manifest(), function ( $m ) use ( $ids, $failed ) {
        return empty( $ids[ $m['key'] ] ) && ! isset( $failed[ $m['key'] ] );
    } ) );

    if ( $left ) {
        $next = bsets_media_import_url();
        $total = count( bsets_media_manifest() );
        echo '<!doctype html><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . esc_url( $next ) . '"><title>Importing images…</title><body style="font:16px system-ui;padding:40px;background:#0a0a0a;color:#eee"><p>Importing Black Sets images to Media Library: ' . (int) ( $total - $left ) . ' of ' . (int) $total . '…</p><p><a style="color:#c5a059" href="' . esc_url( $next ) . '">Continue</a></p>';
        exit;
    }

    wp_safe_redirect( add_query_arg( array( 'action' => 'bsets_setup', '_wpnonce' => wp_create_nonce( 'bsets_setup' ), 'bsets_media' => 'done' ), admin_url( 'admin-post.php' ) ) );
    exit;
} );

