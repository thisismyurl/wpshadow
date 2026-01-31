<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Vertical_Scaling_Limits_Not_Monitored extends Diagnostic_Base{protected static $slug='vertical-scaling-limits-not-monitored';protected static $title='Vertical Scaling Limits Not Monitored';protected static $description='Checks vertical scaling';protected static $family='performance';public static function check(){if(!has_filter('init','monitor_scaling_limits')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Vertical scaling limits not monitored. Track CPU, memory, and disk usage trends to plan capacity upgrades.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/vertical-scaling-limits-not-monitored');}return null;}}
