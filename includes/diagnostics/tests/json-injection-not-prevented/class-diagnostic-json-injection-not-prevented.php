<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_JSON_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='json-injection-not-prevented';protected static $title='JSON Injection Not Prevented';protected static $description='Checks JSON injection prevention';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_json_output')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('JSON injection not prevented. Use wp_json_encode() and ensure JSON output never includes unescaped user input.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/json-injection-not-prevented');}return null;}}
