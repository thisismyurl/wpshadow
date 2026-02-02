<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_IP_Blocking_Rules_Not_Configured extends Diagnostic_Base{protected static $slug='ip-blocking-rules-not-configured';protected static $title='IP Blocking Rules Not Configured';protected static $description='Checks IP blocking';protected static $family='security';public static function check(){if(!get_option('ip_blocklist_configured')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('IP blocking rules not configured. Block known bad IP ranges and implement adaptive IP blocking to prevent brute force and DDoS attacks.','wpshadow'),'severity'=>'medium','threat_level'=>50,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/ip-blocking-rules-not-configured');}return null;}}
