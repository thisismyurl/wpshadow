<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_XSS_Protection_Header_Missing extends Diagnostic_Base{protected static $slug='xss-protection-header-missing';protected static $title='XSS Protection Header Missing';protected static $description='Checks XSS protection';protected static $family='security';public static function check(){if(!has_filter('init','add_xss_protection_header')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('XSS Protection header missing. Add X-XSS-Protection header (legacy support) and Content-Security-Policy for modern browsers.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/xss-protection-header-missing');}return null;}}
