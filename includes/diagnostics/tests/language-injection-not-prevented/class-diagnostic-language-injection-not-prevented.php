<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Language_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='language-injection-not-prevented';protected static $title='Language Injection Not Prevented';protected static $description='Checks language injection';protected static $family='security';public static function check(){if(!has_filter('init','prevent_language_injection')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Language injection not prevented. Validate language codes and prevent invalid locale selection attacks.','wpshadow'),'severity'=>'medium','threat_level'=>30,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/language-injection-not-prevented');}return null;}}
