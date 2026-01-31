<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_User_Agent_Validation_Not_Implemented extends Diagnostic_Base{protected static $slug='user-agent-validation-not-implemented';protected static $title='User Agent Validation Not Implemented';protected static $description='Checks user agent validation';protected static $family='functionality';public static function check(){if(!has_filter('init','validate_user_agent')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('User agent validation not implemented. Detect spoofed user agents and suspicious browser fingerprints.','wpshadow'),'severity'=>'low','threat_level'=>20,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/user-agent-validation-not-implemented');}return null;}}
