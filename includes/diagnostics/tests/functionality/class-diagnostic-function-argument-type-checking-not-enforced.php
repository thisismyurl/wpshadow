<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Function_Argument_Type_Checking_Not_Enforced extends Diagnostic_Base{protected static $slug='function-argument-type-checking-not-enforced';protected static $title='Function Argument Type Checking Not Enforced';protected static $description='Checks type hints';protected static $family='functionality';public static function check(){if(!has_filter('init','enforce_type_hints')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Function argument type checking not enforced. Use typed properties and return types for better code quality.','wpshadow'),'severity'=>'low','threat_level'=>10,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/function-argument-type-checking-not-enforced');}return null;}}
