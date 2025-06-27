<?php
if (!defined('ABSPATH')) {
  define('ABSPATH', __DIR__.'/');
}
function get_shortcode_regex($tagnames=null){
    global $shortcode_tags;
    if (empty($tagnames)) {
        $tagnames = array_keys($shortcode_tags ?? []);
    }
    $tagregexp = implode('|', array_map('preg_quote',$tagnames));
    return '\\['
        .'(\\[?)'
        .'(' . $tagregexp . ')'
        .'(?![\\w-])'
        .'('
        .'[^\\]\\/]*'
        .'(?:'
        .'\\/(?!\\])'
        .'[^\\]\\/]*'
        .')*?'
        .')'
        .'(?:'
        .'(\\/)'
        .'\\]'
        .'|'
        .'\\]'
        .'(?:'
        .'('
        .'[^\\[]*+'
        .'(?:'
        .'\\[(?!\\/\\2\\])'
        .'[^\\[]*+'
        .')*+'
        .')'
        .'\\[\\/\\2\\]'
        .')?'
        .')'
        .'(\\]?)';
}
function get_shortcode_atts_regex(){
    return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
}
function shortcode_parse_atts($text){
    $atts=array();
    $pattern=get_shortcode_atts_regex();
    $text=preg_replace("/[\x{00a0}\x{200b}]+/u",' ',$text);
    if(preg_match_all($pattern,$text,$match,PREG_SET_ORDER)){
        foreach($match as $m){
            if(!empty($m[1])){ $atts[strtolower($m[1])] = stripcslashes($m[2]); }
            elseif(!empty($m[3])){ $atts[strtolower($m[3])] = stripcslashes($m[4]); }
            elseif(!empty($m[5])){ $atts[strtolower($m[5])] = stripcslashes($m[6]); }
            elseif(isset($m[7]) && strlen($m[7])){ $atts[] = stripcslashes($m[7]); }
            elseif(isset($m[8]) && strlen($m[8])){ $atts[] = stripcslashes($m[8]); }
            elseif(isset($m[9])){ $atts[] = stripcslashes($m[9]); }
        }
        foreach($atts as &$value){
            if(str_contains($value,'<')){
                if(1!==preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/',$value)){
                    $value='';
                }
            }
        }
    } else {
        $atts=ltrim($text);
    }
    return $atts;
}
function wp_json_encode($data){ return json_encode($data); }
function esc_attr($text){ return htmlspecialchars($text,ENT_QUOTES); }
?>
