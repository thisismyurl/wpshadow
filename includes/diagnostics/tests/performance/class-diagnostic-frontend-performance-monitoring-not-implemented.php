<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Frontend_Performance_Monitoring_Not_Implemented extends Diagnostic_Base{protected static $slug='frontend-performance-monitoring-not-implemented';protected static $title='Frontend Performance Monitoring Not Implemented';protected static $description='Checks frontend monitoring';protected static $family='performance';public static function check(){if(!has_filter('init','monitor_frontend_performance')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Frontend performance monitoring not implemented. Track Core Web Vitals, load times, and user experience metrics.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/frontend-performance-monitoring-not-implemented');}return null;}}
