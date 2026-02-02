<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Field_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='field-injection-not-prevented';protected static $title='Field Injection Not Prevented';protected static $description='Checks field injection';protected static $family='security';public static function check(){if(!has_filter('init','validate_database_fields')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Field injection not prevented. Always use parameterized queries with exact field names, never concatenate.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/field-injection-not-prevented');}return null;}}
