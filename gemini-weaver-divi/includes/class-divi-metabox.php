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
    public static function ui_html( $post_id ) {
        ob_start();
        ?>
        <textarea id="gwd-prompt-input" style="width:100%;height:100px;"></textarea>
        <input type="hidden" id="gwd-post-id" value="<?php echo esc_attr( $post_id ); ?>" />
        <p><button id="gwd-submit-prompt" class="button button-primary" type="button"><?php esc_html_e( 'Enviar', 'gemini-weaver-divi' ); ?></button></p>
        <div id="gwd-status"></div>
        <div id="gwd-preview-container" style="display:none;">
            <p><strong><?php esc_html_e( 'Preview', 'gemini-weaver-divi' ); ?></strong></p>
            <textarea id="gwd-preview-content" style="width:100%;height:150px;" readonly></textarea>
            <div id="gwd-visual-preview-container" style="display:none;"></div>
            <p><button id="gwd-toggle-visual-preview" class="button" type="button"><?php esc_html_e( 'Toggle Visual Preview', 'gemini-weaver-divi' ); ?></button></p>
            <p>
                <button id="gwd-apply-shortcode" class="button button-primary" type="button"><?php esc_html_e( 'Apply', 'gemini-weaver-divi' ); ?></button>
                <button id="gwd-cancel-shortcode" class="button" type="button"><?php esc_html_e( 'Cancel', 'gemini-weaver-divi' ); ?></button>
            </p>
        </div>
        <p><button id="gwd-history-toggle" class="button" type="button"><?php esc_html_e( 'Show History', 'gemini-weaver-divi' ); ?></button></p>
        <div id="gwd-history-container" style="display:none;"></div>
        <?php
        return ob_get_clean();
    }

    public function render() {
        echo self::ui_html( get_the_ID() );
    }
}

new GWD_Divi_Metabox();
