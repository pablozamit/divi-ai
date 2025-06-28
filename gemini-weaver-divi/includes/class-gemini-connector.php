<?php
/**
 * Handles communication with the Gemini API.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Gemini_Connector {

    /**
     * Retrieve the API key from options or constant.
     *
     * @return string|false
     */
    protected function get_api_key() {
        $key = get_option( 'gwd_gemini_api_key' );
        if ( ! $key && defined( 'GWD_GEMINI_API_KEY' ) ) {
            $key = GWD_GEMINI_API_KEY;
        }
        return $key;
    }

    /**
     * Send a prompt to Gemini and return the plain text response.
     *
     * @param string $prompt Prompt string.
     *
     * @return string|WP_Error Text response or WP_Error on failure.
     */
    public function send_prompt( $prompt ) {
        $api_key = $this->get_api_key();
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', __( 'Gemini API key not configured.', 'gemini-weaver-divi' ) );
        }

        gwd_log( 'Sending prompt: ' . $prompt );

        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt ),
                    ),
                ),
            ),
        );

        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 20,
        );

        $response = wp_remote_post( 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', $args );

        if ( is_wp_error( $response ) ) {
            gwd_log( 'Request error: ' . $response->get_error_message(), 'error' );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( 200 !== $response_code ) {
            gwd_log(
                sprintf(
                    'HTTP %d error from Gemini API. Body: %s',
                    $response_code,
                    is_string( $response_body ) ? $response_body : ''
                ),
                'error'
            );
            return new WP_Error(
                'http_error',
                sprintf( __( 'Unexpected HTTP status %d from Gemini API.', 'gemini-weaver-divi' ), $response_code )
            );
        }

        if ( empty( $response_body ) ) {
            gwd_log( 'Empty response from Gemini API.', 'error' );
            return new WP_Error( 'empty_response', __( 'Empty response from Gemini API.', 'gemini-weaver-divi' ) );
        }

        $data = json_decode( $response_body, true );
        if ( null === $data && JSON_ERROR_NONE !== json_last_error() ) {
            gwd_log( 'JSON decode error: ' . json_last_error_msg() . '. Body: ' . $response_body, 'error' );
            return new WP_Error( 'invalid_json', __( 'Could not decode Gemini response.', 'gemini-weaver-divi' ) );
        }

        if ( isset( $data['error'] ) ) {
            gwd_log( 'Gemini API error: ' . wp_json_encode( $data['error'] ), 'error' );
            return new WP_Error( 'api_error', $data['error']['message'] ?? __( 'Unknown API error.', 'gemini-weaver-divi' ) );
        }

        if ( ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            gwd_log( 'Invalid response from Gemini API. Body: ' . $response_body, 'error' );
            return new WP_Error( 'invalid_response', __( 'Invalid response from Gemini API.', 'gemini-weaver-divi' ) );
        }

        $text = trim( $data['candidates'][0]['content']['parts'][0]['text'] );
        gwd_log( 'Received response: ' . $text );
        return $text;
    }

    /**
     * Generate Divi shortcodes using Gemini API with a helper prompt.
     *
     * @param string $user_prompt User provided prompt.
     *
     * @return string|WP_Error Shortcode string or WP_Error on failure.
     */
    public function generate_divi_shortcodes( $user_prompt ) {
        $prompt = sprintf(
            'Eres un asistente experto en el page builder Divi de WordPress. Tu única función es devolver shortcodes de Divi válidos basados en la petición del usuario. No añadas ninguna explicación, solo el código. Petición: %s',
            $user_prompt
        );

        return $this->send_prompt( $prompt );
    }
}
