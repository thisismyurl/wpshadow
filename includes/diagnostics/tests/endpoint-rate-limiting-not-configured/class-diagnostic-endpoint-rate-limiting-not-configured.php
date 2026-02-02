<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Endpoint_Rate_Limiting_Not_Configured extends Diagnostic_Base{protected static $slug='endpoint-rate-limiting-not-configured';protected static $title='Endpoint Rate Limiting Not Configured';protected static $description='Checks endpoint rate limits';protected static $family='security';public static function check(){if(!has_filter('init','apply_endpoint_rate_limiting')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Endpoint rate limiting not configured. Implement per-endpoint rate limits based on IP and user.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/endpoint-rate-limiting-not-configured');}return null;}}
