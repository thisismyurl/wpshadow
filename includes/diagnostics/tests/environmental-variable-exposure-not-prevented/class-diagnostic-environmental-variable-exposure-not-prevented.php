<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Environmental_Variable_Exposure_Not_Prevented extends Diagnostic_Base{protected static $slug='environmental-variable-exposure-not-prevented';protected static $title='Environmental Variable Exposure Not Prevented';protected static $description='Checks env variable exposure';protected static $family='security';public static function check(){if(!has_filter('wp_headers','hide_environment_variables')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Environment variables exposed. Never echo env variables in HTML/logs. Use .gitignore for .env files and set proper permissions on configuration files.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/environmental-variable-exposure-not-prevented');}return null;}}
