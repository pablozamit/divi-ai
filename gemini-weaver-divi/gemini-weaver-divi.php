<?php
/**
 * Plugin Name: Gemini Weaver Divi
 * Description: Integrates generative design features into the Divi theme.
 * Version: 1.0.0
 * Author: Gemini Weaver
 * Text Domain: gemini-weaver-divi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Plugin path and URL constants.
define( 'GWD_PATH', plugin_dir_path( __FILE__ ) );
define( 'GWD_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if Divi theme is active.
 *
 * @return bool
 */
function gwd_is_divi_active() {
    $theme = wp_get_theme();

    if ( 'Divi' === $theme->get( 'Name' ) || 'Divi' === $theme->get( 'Template' ) ) {
        return true;
    }

    if ( $theme->parent() && 'Divi' === $theme->parent()->get( 'Name' ) ) {
        return true;
    }

    return false;
}

if ( ! gwd_is_divi_active() ) {
    /**
     * Display admin notice if Divi is not active.
     */
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Gemini Weaver Divi requires the Divi theme to be active.', 'gemini-weaver-divi' ) . '</p></div>';
    } );

    // Do not load further if Divi is not active.
    return;
}

// Include metabox class.
require_once GWD_PATH . 'includes/class-divi-metabox.php';
// Include Gemini connector.
require_once GWD_PATH . 'includes/class-gemini-connector.php';
// Divi parser for converting shortcodes to JSON and back.
require_once GWD_PATH . 'includes/class-divi-parser.php';

/**
 * Enqueue scripts and styles on the page editor screen.
 */
function gwd_enqueue_editor_assets( $hook ) {
    global $pagenow;

    if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    $screen = get_current_screen();

    if ( 'page' !== $screen->post_type ) {
        return;
    }

    wp_enqueue_script( 'gwd-main', GWD_URL . 'assets/js/gwd-main.js', array( 'jquery' ), '1.0.0', true );
    wp_localize_script(
        'gwd-main',
        'gwd_ajax',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'gwd_nonce' ),
        )
    );
    wp_enqueue_style( 'gwd-style', GWD_URL . 'assets/css/gwd-style.css', array(), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'gwd_enqueue_editor_assets' );

/**
 * Process prompt sent from AJAX request.
 */
function gwd_process_prompt() {
    check_ajax_referer( 'gwd_nonce', 'nonce' );

    $prompt  = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_send_json( array(
            'status'  => 'error',
            'message' => __( 'Invalid post ID.', 'gemini-weaver-divi' ),
        ) );
    }

    $parser       = new Divi_Parser();
    $current_json = $parser->parse_to_json( $post->post_content );

    $full_prompt = sprintf(
        "Eres un editor de páginas Divi. A continuación te doy la estructura JSON de una página. Modifícala según la petición del usuario y devuelve únicamente el nuevo JSON completo. Estructura actual: %s Petición: '%s'.",
        $current_json,
        $prompt
    );

    $connector     = new Gemini_Connector();
    $json_response = $connector->send_prompt( $full_prompt );

    if ( is_wp_error( $json_response ) ) {
        wp_send_json( array(
            'status'  => 'error',
            'message' => $json_response->get_error_message(),
        ) );
    }

    $clean_json   = trim( $json_response, " \n\r\t`" );
    $shortcode    = $parser->rebuild_from_json( $clean_json );

    wp_send_json( array(
        'status'    => 'success',
        'shortcode' => $shortcode,
    ) );

    wp_die();
}
add_action( 'wp_ajax_gwd_process_prompt', 'gwd_process_prompt' );
