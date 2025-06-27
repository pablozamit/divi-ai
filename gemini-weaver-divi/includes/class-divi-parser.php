<?php
/**
 * Parses and rebuilds Divi shortcodes to/from a simplified JSON structure.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Divi_Parser {

    /**
     * Counter for unique IDs.
     *
     * @var int
     */
    protected $counter = 1;

    /**
     * Convert Divi shortcode string into simplified JSON structure.
     *
     * @param string $shortcode_string Full post_content string.
     * @return string JSON representation.
     */
    public function parse_to_json( $shortcode_string ) {
        $this->counter = 1;
        $structure     = $this->parse_recursive( $shortcode_string );
        return wp_json_encode( $structure );
    }

    /**
     * Recursively parse shortcodes.
     *
     * @param string $content Content to parse.
     * @return array
     */
    protected function parse_recursive( $content ) {
        $pattern = get_shortcode_regex();
        $result  = array();

        while ( preg_match( '/' . $pattern . '/s', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
            $tag          = $matches[2][0];
            $attrs_string = $matches[3][0];
            $inner        = isset( $matches[5][0] ) ? $matches[5][0] : '';
            $offset_end   = $matches[0][1] + strlen( $matches[0][0] );
            $content      = substr( $content, $offset_end );

            $attrs = shortcode_parse_atts( $attrs_string );
            $elem  = array(
                'type' => str_replace( 'et_pb_', '', $tag ),
                'id'   => 'gwd-id-' . $this->counter++,
            );
            if ( ! empty( $attrs ) ) {
                $elem['attrs'] = $attrs;
            }

            if ( '' !== $inner ) {
                if ( preg_match( '/' . $pattern . '/s', $inner ) ) {
                    $elem['content'] = $this->parse_recursive( $inner );
                } else {
                    $elem['content'] = trim( $inner );
                }
            } else {
                $elem['content'] = '';
            }

            $result[] = $elem;
        }

        return $result;
    }

    /**
     * Rebuild Divi shortcodes from JSON structure.
     *
     * @param string|array $json JSON string or decoded array.
     * @return string Shortcode string.
     */
    public function rebuild_from_json( $json ) {
        if ( is_string( $json ) ) {
            $data = json_decode( $json, true );
        } else {
            $data = $json;
        }

        if ( empty( $data ) || ! is_array( $data ) ) {
            return '';
        }

        return $this->build_recursive( $data );
    }

    /**
     * Recursively build shortcode string.
     *
     * @param array $elements Parsed elements.
     * @return string
     */
    protected function build_recursive( $elements ) {
        $output = '';
        foreach ( $elements as $element ) {
            $tag = 'et_pb_' . ( isset( $element['type'] ) ? $element['type'] : '' );
            $attrs = '';
            if ( isset( $element['attrs'] ) && is_array( $element['attrs'] ) ) {
                foreach ( $element['attrs'] as $k => $v ) {
                    $attrs .= sprintf( ' %s="%s"', $k, esc_attr( $v ) );
                }
            }
            $inner = '';
            if ( isset( $element['content'] ) ) {
                if ( is_array( $element['content'] ) ) {
                    $inner = $this->build_recursive( $element['content'] );
                } else {
                    $inner = $element['content'];
                }
            }
            $output .= '[' . $tag . $attrs . ']' . $inner . '[/' . $tag . ']';
        }
        return $output;
    }
}
