<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Broken_Access_Control_Not_Prevented extends Diagnostic_Base{protected static $slug='broken-access-control-not-prevented';protected static $title='Broken Access Control Not Prevented';protected static $description='Checks access control';protected static $family='security';public static function check(){if(!has_filter('init','verify_access_control')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Broken access control not prevented. Always verify permissions at both function and data layer for all operations.','wpshadow'),'severity'=>'high','threat_level'=>85,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/broken-access-control-not-prevented');}return null;}}
