<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_API_Rate_Limiting_Not_Enforced extends Diagnostic_Base{protected static $slug='api-rate-limiting-not-enforced';protected static $title='API Rate Limiting Not Enforced';protected static $description='Checks rate limiting';protected static $family='functionality';public static function check(){if(!has_filter('rest_dispatch_request','check_rate_limit')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('API rate limiting not enforced. Implement per-client request throttling to prevent abuse and DoS attacks.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/api-rate-limiting-not-enforced');}return null;}}
