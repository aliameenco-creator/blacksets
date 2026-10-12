<?php
/**
 * Contact and Subscription Forms for Black Sets.
 * Submissions are saved as private "Enquiries" in wp-admin (post type bsets_enquiry)
 * and emailed to the configured studio address. Zero third-party dependencies.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function bsets_form_definitions() {
    static $forms = null;
    if ( null === $forms ) {
        $file = get_theme_file_path( 'parts/forms.json' );
        $forms = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array();
    }
    return is_array( $forms ) ? $forms : array();
}

function bsets_contact_recipient() {
    $email = sanitize_email( (string) get_option( 'bsets_contact_recipient', '' ) );
    return is_email( $email ) ? $email : 'info@blacklab.com';
}

add_action( 'init', function () {
    register_post_type( 'bsets_enquiry', array(
        'labels'          => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry', 'menu_name' => 'Enquiries', 'all_items' => 'All enquiries', 'edit_item' => 'Enquiry' ),
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => false,
        'menu_icon'       => 'dashicons-email-alt',
        'menu_position'   => 26,
        'supports'        => array( 'title' ),
        'capability_type' => 'post',
        'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
        'map_meta_cap'    => true,
    ) );
} );

/** Settings field for recipient */
add_action( 'admin_init', function () {
    register_setting( 'general', 'bsets_contact_recipient', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => '' ) );
    add_settings_field( 'bsets_contact_recipient', 'Black Sets form recipient', function () {
        echo '<input type="email" class="regular-text" name="bsets_contact_recipient" value="' . esc_attr( get_option( 'bsets_contact_recipient', '' ) ) . '" placeholder="info@blacklab.com"><p class="description">Production enquiries are emailed here and saved in wp-admin under Enquiries.</p>';
    }, 'general' );
} );

add_action( 'add_meta_boxes_bsets_enquiry', function () {
    add_meta_box( 'bsets_enquiry_fields', 'Submitted details', function ( $post ) {
        $values = (array) get_post_meta( $post->ID, '_bsets_fields', true );
        $form_id = get_post_meta( $post->ID, '_bsets_form', true ) ?: 'contact';
        $forms = bsets_form_definitions();
        $form = $forms[ $form_id ] ?? array();
        echo '<table class="widefat striped"><tbody><tr><th style="width:180px">Form</th><td>' . esc_html( $form['title'] ?? ucfirst( $form_id ) ) . '</td></tr>';
        $labels = array_column( $form['fields'] ?? array(), 'label', 'key' );
        foreach ( $values as $k => $v ) { // Every stored value, including fields from earlier theme versions.
            echo '<tr><th>' . esc_html( $labels[ $k ] ?? ucwords( str_replace( '_', ' ', $k ) ) ) . '</th><td style="white-space:pre-wrap">' . esc_html( $v ) . '</td></tr>';
        }
        $mail = get_post_meta( $post->ID, '_bsets_mail', true );
        echo '<tr><th>Email notification</th><td>' . esc_html( $mail ? $mail : 'none' ) . '</td></tr><tr><th>Page URL</th><td>' . esc_html( get_post_meta( $post->ID, '_bsets_page', true ) ) . '</td></tr></tbody></table>';
    }, 'bsets_enquiry', 'normal', 'high' );
} );

add_filter( 'manage_bsets_enquiry_posts_columns', function ( $columns ) {
    return array( 'cb' => $columns['cb'], 'title' => 'Name / Email', 'bsets_form' => 'Form', 'bsets_mail' => 'Email sent', 'date' => 'Received' );
} );
add_action( 'manage_bsets_enquiry_posts_custom_column', function ( $column, $id ) {
    if ( 'bsets_form' === $column ) {
        $f = get_post_meta( $id, '_bsets_form', true ) ?: 'contact';
        echo esc_html( bsets_form_definitions()[ $f ]['title'] ?? ucfirst( $f ) );
    }
    if ( 'bsets_mail' === $column ) { echo esc_html( get_post_meta( $id, '_bsets_mail', true ) ); }
}, 10, 2 );

function bsets_render_form( $form_id = 'contact' ) {
    if ( 'subscribe' === $form_id ) {
        $sub = bsets_form_definitions()['subscribe'] ?? array();
        $placeholder = $sub['fields'][0]['placeholder'] ?? 'Enter your email here*';
        $action = esc_url( admin_url( 'admin-post.php' ) );
        return '<form class="bsets-form bsets-subscribe-form" action="' . $action . '" method="post">' .
               '<input type="hidden" name="action" value="bsets_form_submit">' .
               '<input type="hidden" name="bsets_form" value="subscribe">' .
               '<input type="hidden" name="bsets_time" value="' . time() . '">' .
               '<div style="display:none!important"><input type="text" name="bsets_website" autocomplete="off" tabindex="-1"></div>' .
               '<div class="bsets-sub-wrap">' .
               '<input type="email" name="bsets_email" aria-label="Email" placeholder="' . esc_attr( $placeholder ) . '" required>' .
               '<button type="submit" class="bsets-btn">' . esc_html( $sub['submit'] ?? 'Subscribe Now' ) . '</button>' .
               '</div><div class="bsets-form-status" aria-live="polite"></div></form>';
    }

    $forms = bsets_form_definitions();
    $form = $forms[ $form_id ] ?? ( $forms['contact'] ?? array() );
    $action = esc_url( admin_url( 'admin-post.php' ) );
    $out = '<form class="bsets-form" id="form-' . esc_attr( $form_id ) . '" action="' . $action . '" method="post">';
    $out .= '<input type="hidden" name="action" value="bsets_form_submit">';
    $out .= '<input type="hidden" name="bsets_form" value="' . esc_attr( $form_id ) . '">';
    $out .= '<input type="hidden" name="bsets_time" value="' . time() . '">';
    $out .= '<div style="display:none!important"><input type="text" name="bsets_website" autocomplete="off" tabindex="-1"></div>';
    $out .= '<div class="bsets-form-status" aria-live="polite"></div>';
    $out .= '<div class="bsets-form-grid">';

    foreach ( $form['fields'] ?? array() as $field ) {
        $key = esc_attr( $field['key'] );
        $label = esc_html( $field['label'] );
        $req = ! empty( $field['required'] );
        $type = $field['type'] ?? 'text';
        $full = in_array( $key, array( 'message', 'email' ), true ) ? ' bsets-col-full' : '';

        $out .= '<div class="bsets-form-field' . $full . '">';
        $out .= '<label for="bsets_' . $key . '">' . $label . ( $req ? ' <span class="required">*</span>' : '' ) . '</label>';
        if ( 'textarea' === $type ) {
            $out .= '<textarea id="bsets_' . $key . '" name="bsets_' . $key . '" rows="5"' . ( $req ? ' required' : '' ) . '></textarea>';
        } else {
            $out .= '<input type="' . esc_attr( $type ) . '" id="bsets_' . $key . '" name="bsets_' . $key . '"' . ( $req ? ' required' : '' ) . '>';
        }
        $out .= '</div>';
    }

    $out .= '</div>';
    $out .= '<div class="bsets-form-actions"><button type="submit" class="bsets-btn">' . esc_html( $form['submit'] ?? 'Send' ) . '</button></div>';
    $out .= '</form>';
    return $out;
}

add_shortcode( 'bsets_form', function ( $atts ) {
    $a = shortcode_atts( array( 'id' => 'contact' ), $atts );
    return bsets_render_form( $a['id'] );
} );

function bsets_handle_form() {
    $form_id = isset( $_POST['bsets_form'] ) ? sanitize_key( wp_unslash( $_POST['bsets_form'] ) ) : 'contact';
    $is_ajax = str_contains( (string) ( $_SERVER['HTTP_ACCEPT'] ?? '' ), 'application/json' );

    // Honeypot spam protection
    if ( ! empty( $_POST['bsets_website'] ) ) {
        if ( $is_ajax ) { wp_send_json( array( 'ok' => true, 'message' => 'Thank you!' ) ); }
        wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ?: home_url( '/contact' ) ) );
        exit;
    }

    $values = array();
    $errors = array();

    if ( 'subscribe' === $form_id ) {
        $email = sanitize_email( wp_unslash( $_POST['bsets_email'] ?? '' ) );
        if ( ! is_email( $email ) ) { $errors['email'] = 'Valid email required.'; }
        $values['email'] = $email;
        $title = $email;
    } else {
        $forms = bsets_form_definitions();
        $form = $forms[ $form_id ] ?? array();
        foreach ( $form['fields'] ?? array() as $f ) {
            $raw = wp_unslash( $_POST[ 'bsets_' . $f['key'] ] ?? '' );
            $val = 'textarea' === $f['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
            $values[ $f['key'] ] = $val;
            if ( ! empty( $f['required'] ) && '' === trim( $val ) ) {
                $errors[ $f['key'] ] = $f['label'] . ' is required.';
            }
            if ( 'email' === $f['type'] && '' !== $val && ! is_email( $val ) ) {
                $errors[ $f['key'] ] = 'Please enter a valid email address.';
            }
        }
        $name = trim( ( $values['first_name'] ?? '' ) . ' ' . ( $values['last_name'] ?? '' ) ) ?: ( $values['name'] ?? 'Visitor' );
        $email = $values['email'] ?? '';
        $title = $email ? "{$name} <{$email}>" : $name;
    }

    if ( ! empty( $errors ) ) {
        if ( $is_ajax ) { wp_send_json( array( 'ok' => false, 'message' => 'Please correct the errors.', 'errors' => $errors ), 422 ); }
        wp_safe_redirect( add_query_arg( 'error', '1', wp_get_referer() ?: home_url( '/contact' ) ) );
        exit;
    }

    // Save as private Enquiry post
    $post_id = wp_insert_post( array(
        'post_type'   => 'bsets_enquiry',
        'post_title'  => $title,
        'post_status' => 'private',
    ) );

    $page_url = wp_get_referer() ?: home_url( '/contact' );
    update_post_meta( $post_id, '_bsets_form', $form_id );
    update_post_meta( $post_id, '_bsets_fields', $values );
    update_post_meta( $post_id, '_bsets_page', esc_url( $page_url ) );

    // Send notification email
    $to = bsets_contact_recipient();
    $subject = "[Black Sets] New {$form_id} submission: " . $title;
    $body = "New submission on Black Sets website:\n\n";
    foreach ( $values as $k => $v ) {
        $body .= ucfirst( $k ) . ":\n" . $v . "\n\n";
    }
    $body .= "Submitted from: " . $page_url . "\nTime: " . current_time( 'mysql' ) . "\n";
    $sent = wp_mail( $to, $subject, $body );
    update_post_meta( $post_id, '_bsets_mail', $sent ? 'Sent to ' . $to : 'Failed' );

    $thanks = bsets_form_definitions()[ $form_id ]['thanks'] ?? 'Thanks for submitting!';
    if ( $is_ajax ) {
        wp_send_json( array( 'ok' => true, 'message' => $thanks ) );
    }
    wp_safe_redirect( add_query_arg( 'sent', '1', $page_url ) );
    exit;
}
add_action( 'admin_post_bsets_form_submit', 'bsets_handle_form' );
add_action( 'admin_post_nopriv_bsets_form_submit', 'bsets_handle_form' );

