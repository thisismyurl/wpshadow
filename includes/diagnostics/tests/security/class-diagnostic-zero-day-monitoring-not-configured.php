<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Zero_Day_Monitoring_Not_Configured extends Diagnostic_Base{protected static $slug='zero-day-monitoring-not-configured';protected static $title='Zero-Day Monitoring Not Configured';protected static $description='Checks zero-day monitoring';protected static $family='security';public static function check(){if(!get_option('zeroday_monitoring_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Zero-day monitoring not configured. Subscribe to security advisories from WordPress security organizations.','wpshadow'),'severity'=>'medium','threat_level'=>50,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/zero-day-monitoring-not-configured');}return null;}}
