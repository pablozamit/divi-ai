<?php
/**
 * Admin settings page for Gemini Weaver Divi plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register plugin settings.
 */
function gwd_register_settings() {
    register_setting( 'gwd_settings', 'gwd_gemini_api_key' );
}
add_action( 'admin_init', 'gwd_register_settings' );

/**
 * Render settings page.
 */
function gwd_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Gemini Weaver Settings', 'gemini-weaver-divi' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'gwd_settings' );
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="gwd_gemini_api_key"><?php esc_html_e( 'Gemini API Key', 'gemini-weaver-divi' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="gwd_gemini_api_key" name="gwd_gemini_api_key" value="<?php echo esc_attr( get_option( 'gwd_gemini_api_key' ) ); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Add settings page to the WordPress admin menu.
 */
function gwd_add_settings_page() {
    add_options_page(
        __( 'Gemini Weaver', 'gemini-weaver-divi' ),
        __( 'Gemini Weaver', 'gemini-weaver-divi' ),
        'manage_options',
        'gwd-settings',
        'gwd_settings_page'
    );
}
add_action( 'admin_menu', 'gwd_add_settings_page' );
