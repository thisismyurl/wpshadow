<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Rate_Limiting_On_Forms_Not_Configured extends Diagnostic_Base{protected static $slug='rate-limiting-on-forms-not-configured';protected static $title='Rate Limiting On Forms Not Configured';protected static $description='Checks form rate limiting';protected static $family='security';public static function check(){if(!has_filter('wp_authenticate','check_login_rate_limit')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Form rate limiting not configured. Limit login attempts to 5 per minute per IP to prevent brute force attacks on login, contact, and registration forms.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/rate-limiting-on-forms-not-configured');}return null;}}
