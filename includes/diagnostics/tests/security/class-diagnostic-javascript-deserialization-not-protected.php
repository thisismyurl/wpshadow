<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_JavaScript_Deserialization_Not_Protected extends Diagnostic_Base{protected static $slug='javascript-deserialization-not-protected';protected static $title='JavaScript Deserialization Not Protected';protected static $description='Checks JS deserialization';protected static $family='security';public static function check(){if(!has_filter('init','protect_js_deserialization')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('JavaScript deserialization not protected. Never eval() user input and use JSON.parse() with try-catch.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/javascript-deserialization-not-protected');}return null;}}
