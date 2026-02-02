<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Unsafe_Reflection_Not_Prevented extends Diagnostic_Base{protected static $slug='unsafe-reflection-not-prevented';protected static $title='Unsafe Reflection Not Prevented';protected static $description='Checks unsafe reflection';protected static $family='security';public static function check(){if(!has_filter('init','prevent_unsafe_reflection')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Unsafe reflection not prevented. Never instantiate arbitrary classes or call methods based on user input.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/unsafe-reflection-not-prevented');}return null;}}
