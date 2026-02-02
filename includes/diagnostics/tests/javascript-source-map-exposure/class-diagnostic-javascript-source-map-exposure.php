<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_JavaScript_Source_Map_Exposure extends Diagnostic_Base{protected static $slug='javascript-source-map-exposure';protected static $title='JavaScript Source Map Exposure';protected static $description='Checks source map exposure';protected static $family='security';public static function check(){if(!has_filter('init','prevent_source_map_exposure')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('JavaScript source map exposed in production. Disable source maps on production or require authentication.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/javascript-source-map-exposure');}return null;}}
