<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Traffic_Analysis_Not_Prevented extends Diagnostic_Base{protected static $slug='traffic-analysis-not-prevented';protected static $title='Traffic Analysis Not Prevented';protected static $description='Checks traffic analysis';protected static $family='security';public static function check(){if(!has_filter('init','prevent_traffic_analysis')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Traffic analysis not prevented. Use uniform response sizes and avoid timing patterns in APIs to prevent inference attacks.','wpshadow'),'severity'=>'medium','threat_level'=>30,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/traffic-analysis-not-prevented');}return null;}}
