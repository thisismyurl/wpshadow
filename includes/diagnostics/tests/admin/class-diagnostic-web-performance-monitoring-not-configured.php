<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Web_Performance_Monitoring_Not_Configured extends Diagnostic_Base{protected static $slug='web-performance-monitoring-not-configured';protected static $title='Web Performance Monitoring Not Configured';protected static $description='Checks performance monitoring';protected static $family='admin';public static function check(){if(!get_option('performance_monitoring_service')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Web performance monitoring not configured. Set up monitoring with DataDog, Pingdom, or New Relic to track real-time performance metrics.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/web-performance-monitoring-not-configured');}return null;}}
