<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Log_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='log-injection-not-prevented';protected static $title='Log Injection Not Prevented';protected static $description='Checks log injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_log_entries')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Log injection not prevented. Sanitize user input before logging and use structured logging formats.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/log-injection-not-prevented');}return null;}}
