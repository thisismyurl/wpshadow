<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Template_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='template-injection-not-prevented';protected static $title='Template Injection Not Prevented';protected static $description='Checks template injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_template_input')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Template injection not prevented. Never include user input directly in template expressions like {{}} or ${}. ','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/template-injection-not-prevented');}return null;}}
