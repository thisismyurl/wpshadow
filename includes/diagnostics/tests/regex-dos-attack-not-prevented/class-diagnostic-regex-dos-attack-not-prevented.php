<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Regex_DoS_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='regex-dos-attack-not-prevented';protected static $title='Regex DoS Attack Not Prevented';protected static $description='Checks ReDoS prevention';protected static $family='security';public static function check(){if(!has_filter('init','validate_regex_patterns')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Regex DoS attack not prevented. Avoid complex nested quantifiers and use timeouts on regex operations.','wpshadow'),'severity'=>'high','threat_level'=>55,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/regex-dos-attack-not-prevented');}return null;}}
