<?php
require __DIR__ . '/wp-stubs.php';
require __DIR__ . '/../gemini-weaver-divi/includes/class-divi-parser.php';

// Register expected shortcodes for regex generation.
$shortcode_tags = array(
    'et_pb_section' => 'dummy',
    'et_pb_row' => 'dummy',
    'et_pb_column' => 'dummy',
    'et_pb_text' => 'dummy',
);
?>
