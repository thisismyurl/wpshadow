<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Load_Balancer_Health_Checks_Not_Configured extends Diagnostic_Base{protected static $slug='load-balancer-health-checks-not-configured';protected static $title='Load Balancer Health Checks Not Configured';protected static $description='Checks health checks';protected static $family='performance';public static function check(){if(!has_filter('init','configure_health_checks')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Load balancer health checks not configured. Implement endpoints that verify backend availability without side effects.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/load-balancer-health-checks-not-configured');}return null;}}
