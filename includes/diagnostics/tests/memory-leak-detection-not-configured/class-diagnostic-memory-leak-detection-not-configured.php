<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Memory_Leak_Detection_Not_Configured extends Diagnostic_Base{protected static $slug='memory-leak-detection-not-configured';protected static $title='Memory Leak Detection Not Configured';protected static $description='Checks memory leak detection';protected static $family='admin';public static function check(){if(!has_filter('init','detect_memory_leaks')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Memory leak detection not configured. Monitor memory usage trends and implement tools like XDebug profiling to detect leaks.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/memory-leak-detection-not-configured');}return null;}}
