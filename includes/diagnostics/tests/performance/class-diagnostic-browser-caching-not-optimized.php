<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Browser_Caching_Not_Optimized extends Diagnostic_Base{protected static $slug='browser-caching-not-optimized';protected static $title='Browser Caching Not Optimized';protected static $description='Checks browser caching';protected static $family='performance';public static function check(){if(!has_filter('init','optimize_browser_cache')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Browser caching not optimized. Set Cache-Control headers with appropriate max-age for static assets.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/browser-caching-not-optimized');}return null;}}
