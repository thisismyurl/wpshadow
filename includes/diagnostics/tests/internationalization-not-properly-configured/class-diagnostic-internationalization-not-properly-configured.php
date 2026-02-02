<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Internationalization_Not_Properly_Configured extends Diagnostic_Base{protected static $slug='internationalization-not-properly-configured';protected static $title='Internationalization Not Properly Configured';protected static $description='Checks i18n';protected static $family='functionality';public static function check(){if(!has_filter('init','validate_i18n_setup')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Internationalization not properly configured. Use load_plugin_textdomain() and translate all user-facing strings.','wpshadow'),'severity'=>'low','threat_level'=>10,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/internationalization-not-properly-configured');}return null;}}
