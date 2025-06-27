<?php
/**
 * REST API endpoints for Gemini Weaver Divi plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function gwd_register_rest_routes() {
    register_rest_route( 'gwd/v1', '/gemini-key', array(
        'methods'             => array( 'GET', 'POST' ),
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
        'callback'            => 'gwd_handle_gemini_key',
    ) );
}
add_action( 'rest_api_init', 'gwd_register_rest_routes' );

function gwd_handle_gemini_key( WP_REST_Request $request ) {
    if ( 'GET' === $request->get_method() ) {
        $key = get_option( 'gwd_gemini_api_key', '' );
        return rest_ensure_response( array( 'key' => $key ) );
    }

    if ( 'POST' === $request->get_method() ) {
        $key = sanitize_text_field( $request->get_param( 'key' ) );
        update_option( 'gwd_gemini_api_key', $key );
        return rest_ensure_response( array( 'success' => true ) );
    }

    return rest_ensure_response( array( 'success' => false ) );
}
