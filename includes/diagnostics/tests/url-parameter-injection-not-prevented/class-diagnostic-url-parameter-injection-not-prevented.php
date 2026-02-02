<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_URL_Parameter_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='url-parameter-injection-not-prevented';protected static $title='URL Parameter Injection Not Prevented';protected static $description='Checks URL injection';protected static $family='security';public static function check(){if(!has_filter('init','validate_url_parameters')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('URL parameter injection not prevented. Whitelist allowed parameters and validate/sanitize all URL query strings.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/url-parameter-injection-not-prevented');}return null;}}
