<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_HTTP_Strict_Transport_Security_Not_Set extends Diagnostic_Base{protected static $slug='http-strict-transport-security-not-set';protected static $title='HTTP Strict Transport Security Not Set';protected static $description='Checks HSTS';protected static $family='security';public static function check(){if(!has_filter('init','set_hsts_header')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('HSTS header not set. Add Strict-Transport-Security header to enforce HTTPS for all connections.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/http-strict-transport-security-not-set');}return null;}}
