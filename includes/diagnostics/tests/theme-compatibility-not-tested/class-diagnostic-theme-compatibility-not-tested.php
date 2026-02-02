<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Theme_Compatibility_Not_Tested extends Diagnostic_Base{protected static $slug='theme-compatibility-not-tested';protected static $title='Theme Compatibility Not Tested';protected static $description='Checks theme compatibility testing';protected static $family='admin';public static function check(){if(!get_option('theme_compatibility_test_date')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Theme compatibility not tested. Test plugins with all active themes to ensure compatibility and prevent conflicts or display issues.','wpshadow'),'severity'=>'low','threat_level'=>15,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/theme-compatibility-not-tested');}return null;}}
