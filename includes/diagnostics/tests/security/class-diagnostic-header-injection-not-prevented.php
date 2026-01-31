<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Header_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='header-injection-not-prevented';protected static $title='Header Injection Not Prevented';protected static $description='Checks header injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_http_headers')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Header injection not prevented. Never include user input in HTTP headers without sanitization.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/header-injection-not-prevented');}return null;}}
