<?php
/**
 * One-click Setup for Black Sets.
 * Appearance > Black Sets Setup runs media import, creates all editable block pages,
 * creates the navigation menu, configures permalinks and sets up the front page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
    add_theme_page(
        'Black Sets Setup',
        'Black Sets Setup',
        'manage_options',
        'bsets-setup',
        'bsets_setup_page_render'
    );
} );

function bsets_setup_page_render() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $pending_pages = bsets_pages_pending();
    $pending_media = bsets_media_pending();
    $media_total = count( bsets_media_manifest() );
    $media_imported = $media_total - count( $pending_media );
    $is_complete = empty( $pending_pages ) && empty( $pending_media );
    ?>
    <div class="wrap" style="max-width:850px;margin-top:24px">
      <h1>Black Sets Setup &amp; Page Generator</h1>
      <p style="font-size:15px;color:#4b5563">
        Configure Black Sets in one click: imports all images into the WordPress Media Library, creates all 9 editable block pages under <strong>Pages</strong>, sets up the Primary Menu, and configures the homepage.
      </p>

      <div style="background:#fff;border:1px solid #ccd0d4;padding:24px;border-radius:4px;margin-top:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
        <h2 style="margin-top:0">Setup Status</h2>
        <table class="widefat striped" style="margin-bottom:20px">
          <tbody>
            <tr>
              <th style="width:240px">Media Library Import</th>
              <td>
                <?php if ( empty( $pending_media ) ) : ?>
                  <span style="color:#10b981;font-weight:600">✓ All <?php echo (int) $media_total; ?> assets imported</span>
                <?php else : ?>
                  <span style="color:#d97706;font-weight:600"><?php echo (int) $media_imported; ?> of <?php echo (int) $media_total; ?> assets imported</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Editable Pages</th>
              <td>
                <?php if ( empty( $pending_pages ) ) : ?>
                  <span style="color:#10b981;font-weight:600">✓ All pages active and editable under Pages</span>
                <?php else : ?>
                  <span style="color:#d97706;font-weight:600"><?php echo count( $pending_pages ); ?> page(s) pending creation</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Primary Menu</th>
              <td>
                <?php if ( get_option( 'bsets_menu_created' ) ) : ?>
                  <span style="color:#10b981;font-weight:600">✓ Created (Appearance > Menus)</span>
                <?php else : ?>
                  <span style="color:#6b7280">Pending setup</span>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>

        <?php if ( $is_complete ) : ?>
          <div style="background:#f0fdf4;border-left:4px solid #10b981;padding:12px 16px;margin-bottom:20px">
            <p style="margin:0;color:#166534;font-weight:600">Black Sets is fully configured. All pages are editable in the WordPress block editor.</p>
          </div>
        <?php endif; ?>

        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
          <?php wp_nonce_field( 'bsets_setup' ); ?>
          <input type="hidden" name="action" value="bsets_setup">
          <button type="submit" class="button button-primary button-hero" style="background:#0a0a0a;border-color:#0a0a0a">
            <?php echo $is_complete ? 'Re-run Setup (Safe &amp; Idempotent)' : 'Complete Black Sets Setup'; ?>
          </button>
        </form>
      </div>
    </div>
    <?php
}

add_action( 'admin_post_bsets_setup', function () {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed.' ); }
    check_admin_referer( 'bsets_setup' );

    // If media is pending, start media sideload first
    if ( ! empty( bsets_media_pending() ) ) {
        wp_safe_redirect( bsets_media_import_url() );
        exit;
    }

    // Configure permalinks
    global $wp_rewrite;
    if ( get_option( 'permalink_structure' ) !== '/%postname%/' ) {
        update_option( 'permalink_structure', '/%postname%/' );
        $wp_rewrite->set_permalink_structure( '/%postname%/' );
        $wp_rewrite->flush_rules( false );
    }

    update_option( 'blogname', 'Black Sets' );
    update_option( 'blogdescription', 'Film Set Design, Fabrication & 3D Virtual Production' );

    // Seed pages and menu
    bsets_pages_seed();

    wp_safe_redirect( add_query_arg( array( 'page' => 'bsets-setup', 'bsets_setup_done' => '1' ), admin_url( 'themes.php' ) ) );
    exit;
} );

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    if ( isset( $_GET['bsets_setup_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        echo '<div class="notice notice-success is-dismissible"><p><strong>Black Sets Setup Complete:</strong> all pages created under Pages, navigation menu configured, and front page active.</p></div>';
    }
} );

