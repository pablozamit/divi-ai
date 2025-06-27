<?php
/**
 * Divi metabox for Gemini Weaver Divi plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class GWD_Divi_Metabox {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
    }

    /**
     * Register the metabox.
     */
    public function add_metabox() {
        add_meta_box(
            'gwd-divi-metabox',
            __( 'Diseñar con Gemini', 'gemini-weaver-divi' ),
            array( $this, 'render' ),
            'page',
            'side',
            'default'
        );
    }

    /**
     * Render the metabox HTML.
     */
    public function render() {
        echo '<textarea id="gwd-prompt-input" style="width:100%;height:100px;"></textarea>';
        echo '<input type="hidden" id="gwd-post-id" value="' . get_the_ID() . '" />';
        echo '<p><button id="gwd-submit-prompt" class="button button-primary" type="button">' . esc_html__( 'Enviar', 'gemini-weaver-divi' ) . '</button></p>';
        echo '<div id="gwd-status"></div>';
    }
}

new GWD_Divi_Metabox();
