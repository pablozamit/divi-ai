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
// Settings page for API configuration.
require_once GWD_PATH . 'includes/gwd-settings.php';
// REST API endpoints for key management.
require_once GWD_PATH . 'includes/gwd-rest-api.php';

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
 * Enqueue assets in Divi Visual Builder (frontend).
 */
function gwd_enqueue_frontend_assets() {
    if ( is_page() && isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'] ) {
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
}
add_action( 'wp_enqueue_scripts', 'gwd_enqueue_frontend_assets' );

/**
 * Render Gemini Weaver UI in the Visual Builder.
 */
function gwd_render_frontend_panel() {
    if ( is_page() && isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'] ) {
        echo '<div id="gwd-builder-panel">';
        echo GWD_Divi_Metabox::ui_html( get_the_ID() );
        echo '</div>';
    }
}
add_action( 'wp_footer', 'gwd_render_frontend_panel' );

/**
 * Recursively find an element by ID.
 *
 * @param array $elements Parsed elements.
 * @param string $search_id Element ID to find.
 * @return array|null Reference to element or null.
 */
function &gwd_get_element_by_id( &$elements, $search_id ) {
    foreach ( $elements as &$el ) {
        if ( isset( $el['id'] ) && $el['id'] === $search_id ) {
            return $el;
        }
        if ( isset( $el['content'] ) && is_array( $el['content'] ) ) {
            $found = &gwd_get_element_by_id( $el['content'], $search_id );
            if ( null !== $found ) {
                return $found;
            }
        }
    }
    $null = null;
    return $null;
}

/**
 * Replace element by ID.
 *
 * @param array $elements Parsed elements.
 * @param string $search_id Target ID.
 * @param array $replacement Replacement element.
 * @return bool
 */
function gwd_replace_element_by_id( &$elements, $search_id, $replacement ) {
    foreach ( $elements as &$el ) {
        if ( isset( $el['id'] ) && $el['id'] === $search_id ) {
            $el = $replacement;
            return true;
        }
        if ( isset( $el['content'] ) && is_array( $el['content'] ) ) {
            if ( gwd_replace_element_by_id( $el['content'], $search_id, $replacement ) ) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Find element ID by comparing structure (ignoring IDs).
 *
 * @param array $elements Elements tree.
 * @param array $target   Target element.
 * @return string|false   ID if found.
 */
function gwd_find_element_id_by_structure( $elements, $target ) {
    foreach ( $elements as $el ) {
        $tmp_el = $el;
        $tmp_t  = $target;
        unset( $tmp_el['id'], $tmp_t['id'] );
        if ( $tmp_el == $tmp_t ) {
            return isset( $el['id'] ) ? $el['id'] : false;
        }
        if ( isset( $el['content'] ) && is_array( $el['content'] ) ) {
            $found = gwd_find_element_id_by_structure( $el['content'], $target );
            if ( $found ) {
                return $found;
            }
        }
    }
    return false;
}

/**
 * Process prompt sent from AJAX request.
 */
function gwd_process_prompt() {
    check_ajax_referer( 'gwd_nonce', 'nonce' );

    $prompt  = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    $element_id = isset( $_POST['element_id'] ) ? sanitize_text_field( wp_unslash( $_POST['element_id'] ) ) : '';
    $element_sc = isset( $_POST['element_shortcode'] ) ? wp_unslash( $_POST['element_shortcode'] ) : '';

    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_send_json( array(
            'status'  => 'error',
            'message' => __( 'Invalid post ID.', 'gemini-weaver-divi' ),
        ) );
    }

    $parser       = new Divi_Parser();
    $current_json = $parser->parse_to_json( $post->post_content );
    $current_data = json_decode( $current_json, true );

    if ( $element_id ) {
        $target = &gwd_get_element_by_id( $current_data, $element_id );
    } elseif ( $element_sc ) {
        $sc_json   = $parser->parse_to_json( $element_sc );
        $sc_data   = json_decode( $sc_json, true );
        $element_id = $sc_data ? gwd_find_element_id_by_structure( $current_data, $sc_data[0] ) : '';
        $target     = $element_id ? gwd_get_element_by_id( $current_data, $element_id ) : null;
    }

    if ( isset( $target ) && is_array( $target ) ) {
        $target_json = wp_json_encode( $target );
        $full_prompt = sprintf(
            "Eres un editor de páginas Divi. A continuación te doy la estructura JSON de un módulo con id %s. Modifícalo según la petición del usuario y devuelve únicamente el JSON actualizado de ese módulo. Elemento actual: %s Petición: '%s'.",
            $element_id,
            $target_json,
            $prompt
        );
    } else {
        $full_prompt = sprintf(
            "Eres un editor de páginas Divi. A continuación te doy la estructura JSON de una página. Modifícala según la petición del usuario y devuelve únicamente el nuevo JSON completo. Estructura actual: %s Petición: '%s'.",
            $current_json,
            $prompt
        );
    }

    $connector     = new Gemini_Connector();
    $json_response = $connector->send_prompt( $full_prompt );

    if ( is_wp_error( $json_response ) ) {
        wp_send_json( array(
            'status'  => 'error',
            'message' => $json_response->get_error_message(),
        ) );
    }

    $clean_json = trim( $json_response, " \n\r\t`" );
    if ( isset( $target ) && is_array( $target ) ) {
        $new_element = json_decode( $clean_json, true );
        if ( null !== $new_element ) {
            gwd_replace_element_by_id( $current_data, $element_id, $new_element );
            $clean_json = wp_json_encode( $current_data );
        }
    }

    $shortcode = $parser->rebuild_from_json( $clean_json );

    $preview_html = do_shortcode( $shortcode );

    $history_item = array(
        'prompt'    => $prompt,
        'timestamp' => current_time( 'mysql' ),
    );
    add_post_meta( $post_id, '_gwd_prompt_history', wp_json_encode( $history_item ) );

    wp_send_json( array(
        'status'       => 'success',
        'shortcode'    => $shortcode,
        'preview_html' => $preview_html,
    ) );

    wp_die();
}
add_action( 'wp_ajax_gwd_process_prompt', 'gwd_process_prompt' );

/**
 * Retrieve prompt history for a post.
 */
function gwd_get_prompt_history() {
    check_ajax_referer( 'gwd_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    $post    = get_post( $post_id );
    if ( ! $post ) {
        wp_send_json( array(
            'status'  => 'error',
            'message' => __( 'Invalid post ID.', 'gemini-weaver-divi' ),
        ) );
    }

    $history_raw = get_post_meta( $post_id, '_gwd_prompt_history', false );
    $history     = array();
    if ( $history_raw ) {
        foreach ( $history_raw as $item ) {
            $decoded = json_decode( $item, true );
            if ( $decoded ) {
                $history[] = $decoded;
            }
        }
    }

    wp_send_json( array(
        'status'  => 'success',
        'history' => $history,
    ) );

    wp_die();
}
add_action( 'wp_ajax_gwd_get_history', 'gwd_get_prompt_history' );
