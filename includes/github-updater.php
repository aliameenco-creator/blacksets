<?php
/**
 * Numbered GitHub release updater for Black Sets theme.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bsets_update_api() {
    // Repository to be assigned when confirmed
    return 'https://api.github.com/repos/aliameenco-creator/blacksets';
}

function bsets_update_headers() {
    $headers = array( 'Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28', 'User-Agent' => 'Black-Sets-WordPress-Updater' );
    if ( defined( 'BSETS_GITHUB_TOKEN' ) && is_string( BSETS_GITHUB_TOKEN ) && BSETS_GITHUB_TOKEN !== '' ) {
        $headers['Authorization'] = 'Bearer ' . BSETS_GITHUB_TOKEN;
    }
    return $headers;
}

function bsets_release_validate( $data ) {
    if ( ! is_array( $data ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) || ! isset( $data['tag_name'], $data['assets'] ) || ! preg_match( '/^v(\d+\.\d+\.\d+)$/D', $data['tag_name'], $match ) ) {
        return new WP_Error( 'release', 'No valid stable numbered release is available.' );
    }
    foreach ( $data['assets'] as $asset ) {
        if ( 'black-sets.zip' !== ( $asset['name'] ?? '' ) ) { continue; }
        if ( empty( $asset['id'] ) || ! is_int( $asset['id'] ) || ! preg_match( '/^sha256:([a-f0-9]{64})$/D', $asset['digest'] ?? '', $digest ) || empty( $asset['size'] ) || $asset['size'] > 80000000 ) {
            return new WP_Error( 'asset', 'Release package missing valid digest or exceeds size.' );
        }
        return array( 'version' => $match[1], 'package' => bsets_update_api() . '/releases/assets/' . $asset['id'], 'sha256' => $digest[1], 'size' => (int) $asset['size'], 'notes' => is_string( $data['body'] ?? null ) ? $data['body'] : '' );
    }
    return new WP_Error( 'package', 'The release must include an uploaded black-sets.zip asset.' );
}

function bsets_release_check( $force = false ) {
    if ( ! $force ) {
        $cached = get_site_transient( 'bsets_github_release' );
        if ( is_array( $cached ) || is_wp_error( $cached ) ) { return $cached; }
    }
    $response = wp_safe_remote_get( bsets_update_api() . '/releases/latest', array( 'headers' => bsets_update_headers(), 'timeout' => 15, 'redirection' => 0, 'limit_response_size' => 1000000 ) );
    if ( is_wp_error( $response ) ) {
        $error = new WP_Error( 'network', 'GitHub could not be reached.' );
        set_site_transient( 'bsets_github_release', $error, 10 * MINUTE_IN_SECONDS );
        return $error;
    }
    if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
        $error = new WP_Error( 'github', 'No release found.' );
        set_site_transient( 'bsets_github_release', $error, 10 * MINUTE_IN_SECONDS );
        return $error;
    }
    $release = bsets_release_validate( json_decode( wp_remote_retrieve_body( $response ), true ) );
    set_site_transient( 'bsets_github_release', $release, is_wp_error( $release ) ? 10 * MINUTE_IN_SECONDS : HOUR_IN_SECONDS );
    return $release;
}

function bsets_release_updates( $transient ) {
    if ( ! is_object( $transient ) ) { return $transient; }
    if ( ! is_admin() && ! wp_doing_cron() && ! is_array( get_site_transient( 'bsets_github_release' ) ) ) { return $transient; }
    $theme = wp_get_theme( 'black-sets' );
    $release = bsets_release_check();
    if ( is_wp_error( $release ) ) { unset( $transient->response['black-sets'] ); return $transient; }
    $item = array( 'theme' => 'black-sets', 'new_version' => $release['version'], 'url' => 'https://github.com/aliameenco-creator/blacksets/releases', 'package' => $release['package'] );
    if ( version_compare( $release['version'], $theme->get( 'Version' ), '>' ) ) {
        $transient->response['black-sets'] = $item;
        unset( $transient->no_update['black-sets'] );
    } else {
        unset( $transient->response['black-sets'] );
        $transient->no_update['black-sets'] = $item;
    }
    return $transient;
}
add_filter( 'site_transient_update_themes', 'bsets_release_updates' );

