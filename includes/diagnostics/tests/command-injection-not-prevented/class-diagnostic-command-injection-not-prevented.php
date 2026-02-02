<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Command_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='command-injection-not-prevented';protected static $title='Command Injection Not Prevented';protected static $description='Checks command injection prevention';protected static $family='security';public static function check(){if(!has_filter('init','escape_shell_commands')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Command injection not prevented. Use escapeshellarg/escapeshellcmd and avoid system() calls with user input. Use safer alternatives like WordPress hooks.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/command-injection-not-prevented');}return null;}}
