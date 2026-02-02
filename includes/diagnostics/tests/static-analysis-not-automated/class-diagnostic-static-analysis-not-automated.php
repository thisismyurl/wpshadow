<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Static_Analysis_Not_Automated extends Diagnostic_Base{protected static $slug='static-analysis-not-automated';protected static $title='Static Analysis Not Automated';protected static $description='Checks static analysis';protected static $family='admin';public static function check(){if(!has_filter('init','run_static_analysis')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Static analysis not automated. Use PHPStan, Psalm, or similar tools to catch bugs before runtime.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/static-analysis-not-automated');}return null;}}
