<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Unused_CSS_Not_Removed extends Diagnostic_Base{protected static $slug='unused-css-not-removed';protected static $title='Unused CSS Not Removed';protected static $description='Checks unused CSS removal';protected static $family='performance';public static function check(){if(!has_filter('wp_head','remove_unused_css')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Unused CSS not removed. Use PurgeCSS or similar tools to remove unused styles and reduce CSS file sizes by 50-80%.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/unused-css-not-removed');}return null;}}
