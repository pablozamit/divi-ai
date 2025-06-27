<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Log a message to the Gemini Weaver log file.
 *
 * @param string $message Message to log.
 * @param string $level   Log level (info, error, etc).
 */
function gwd_log( $message, $level = 'info' ) {
    $upload_dir = wp_upload_dir();
    $log_file   = trailingslashit( $upload_dir['basedir'] ) . 'gwd.log';

    $entry = sprintf( "[%s] [%s] %s\n", current_time( 'mysql' ), strtoupper( $level ), $message );
    error_log( $entry, 3, $log_file );
}

/**
 * Retrieve log entries.
 *
 * @param bool $errors_only Whether to return only error entries.
 * @return array Array of log lines.
 */
function gwd_get_log_entries( $errors_only = false ) {
    $upload_dir = wp_upload_dir();
    $log_file   = trailingslashit( $upload_dir['basedir'] ) . 'gwd.log';

    if ( ! file_exists( $log_file ) ) {
        return array();
    }

    $lines = file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    if ( ! $lines ) {
        return array();
    }

    if ( $errors_only ) {
        $lines = array_filter(
            $lines,
            function ( $line ) {
                return false !== stripos( $line, 'ERROR' );
            }
        );
    }

    return array_reverse( $lines );
}
