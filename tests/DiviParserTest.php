<?php
use PHPUnit\Framework\TestCase;

class DiviParserTest extends TestCase {
    public function testParseToJsonNestedShortcodes() {
        $parser = new Divi_Parser();
        $shortcode = '[et_pb_section foo="bar"][et_pb_row][et_pb_column type="text"][et_pb_text]Hi[/et_pb_text][/et_pb_column][/et_pb_row][/et_pb_section]';
        $json = $parser->parse_to_json($shortcode);
        $data = json_decode($json, true);
        $expected = [
            [
                'type' => 'section',
                'id'   => 'gwd-id-1',
                'attrs' => ['foo' => 'bar'],
                'content' => [
                    [
                        'type' => 'row',
                        'id'   => 'gwd-id-2',
                        'content' => [
                            [
                                'type' => 'column',
                                'id'   => 'gwd-id-3',
                                'attrs' => ['type' => 'text'],
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'id'   => 'gwd-id-4',
                                        'content' => 'Hi'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $data);
    }

    public function testRoundTripIntegrity() {
        $parser = new Divi_Parser();
        $shortcode = '[et_pb_section][et_pb_row][et_pb_column][et_pb_text]Hello[/et_pb_text][/et_pb_column][/et_pb_row][/et_pb_section]';
        $json = $parser->parse_to_json($shortcode);
        $rebuilt = $parser->rebuild_from_json($json);
        $this->assertEquals($shortcode, $rebuilt);
    }
}
?>
