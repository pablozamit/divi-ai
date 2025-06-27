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
function gwd_render_api_settings() {
    ?>
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
    <?php
}

function gwd_render_logs_page() {
    $errors_only = isset( $_GET['errors_only'] ) && '1' === $_GET['errors_only'];
    $logs        = gwd_get_log_entries( $errors_only );
    ?>
    <form method="get" style="margin-bottom:15px;">
        <input type="hidden" name="page" value="gwd-settings" />
        <input type="hidden" name="tab" value="logs" />
        <label>
            <input type="checkbox" name="errors_only" value="1" <?php checked( $errors_only ); ?> />
            <?php esc_html_e( 'Show only errors', 'gemini-weaver-divi' ); ?>
        </label>
        <?php submit_button( __( 'Filter', 'gemini-weaver-divi' ), 'secondary', '', false ); ?>
    </form>
    <pre style="max-height:400px;overflow:auto;background:#fff;padding:10px;border:1px solid #ccc;">
<?php echo esc_html( implode( "\n", $logs ) ); ?>
    </pre>
    <?php
}

function gwd_settings_page() {
    $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Gemini Weaver Settings', 'gemini-weaver-divi' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=gwd-settings&tab=settings" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'API Settings', 'gemini-weaver-divi' ); ?>
            </a>
            <a href="?page=gwd-settings&tab=logs" class="nav-tab <?php echo 'logs' === $tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Logs', 'gemini-weaver-divi' ); ?>
            </a>
        </h2>
        <?php
        if ( 'logs' === $tab ) {
            gwd_render_logs_page();
        } else {
            gwd_render_api_settings();
        }
        ?>
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
