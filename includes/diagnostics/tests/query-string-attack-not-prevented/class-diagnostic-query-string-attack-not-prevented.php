<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Query_String_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='query-string-attack-not-prevented';protected static $title='Query String Attack Not Prevented';protected static $description='Checks query string attacks';protected static $family='security';public static function check(){if(!has_filter('init','validate_query_strings')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Query string attack not prevented. Validate and sanitize all query parameters using whitelisting approach.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/query-string-attack-not-prevented');}return null;}}
