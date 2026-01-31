<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Sensitive_Data_Exposure_In_Logs extends Diagnostic_Base{protected static $slug='sensitive-data-exposure-in-logs';protected static $title='Sensitive Data Exposure In Logs';protected static $description='Checks log security';protected static $family='security';public static function check(){if(!get_option('log_sanitization_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Sensitive data exposed in logs. Mask passwords, tokens, and PII in all log outputs.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/sensitive-data-exposure-in-logs');}return null;}}
