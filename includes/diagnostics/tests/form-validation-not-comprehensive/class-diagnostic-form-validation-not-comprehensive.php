<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Form_Validation_Not_Comprehensive extends Diagnostic_Base{protected static $slug='form-validation-not-comprehensive';protected static $title='Form Validation Not Comprehensive';protected static $description='Checks form validation';protected static $family='functionality';public static function check(){if(!has_filter('wp_head','validate_all_forms')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Form validation not comprehensive. Validate all input on both client and server side using HTML5 validation and backend checks.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/form-validation-not-comprehensive');}return null;}}
