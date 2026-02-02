<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Server_Response_Timing_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='server-response-timing-attack-not-prevented';protected static $title='Server Response Timing Attack Not Prevented';protected static $description='Checks timing attacks';protected static $family='security';public static function check(){if(!has_filter('init','prevent_timing_attacks')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Server response timing attack not prevented. Use constant-time comparison functions like hash_equals() for sensitive data.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/server-response-timing-attack-not-prevented');}return null;}}
