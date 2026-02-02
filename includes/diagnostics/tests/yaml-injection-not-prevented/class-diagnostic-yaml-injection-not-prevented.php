<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_YAML_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='yaml-injection-not-prevented';protected static $title='YAML Injection Not Prevented';protected static $description='Checks YAML injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_yaml_input')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('YAML injection not prevented. Use safe_load() instead of load() and avoid parsing untrusted YAML.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/yaml-injection-not-prevented');}return null;}}
