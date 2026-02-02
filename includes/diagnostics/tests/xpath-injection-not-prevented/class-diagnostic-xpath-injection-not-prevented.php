<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_XPATH_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='xpath-injection-not-prevented';protected static $title='XPATH Injection Not Prevented';protected static $description='Checks XPATH injection prevention';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_xpath_queries')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('XPATH injection not prevented. Sanitize all XPATH queries and use parameterized queries to prevent injection attacks on XML processing.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/xpath-injection-not-prevented');}return null;}}
