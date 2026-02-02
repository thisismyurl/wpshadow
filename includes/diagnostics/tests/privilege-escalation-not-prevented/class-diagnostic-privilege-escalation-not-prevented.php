<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Privilege_Escalation_Not_Prevented extends Diagnostic_Base{protected static $slug='privilege-escalation-not-prevented';protected static $title='Privilege Escalation Not Prevented';protected static $description='Checks privilege escalation';protected static $family='security';public static function check(){if(!has_filter('init','prevent_privilege_escalation')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Privilege escalation not prevented. Strictly validate user roles and never trust user input for capability checks.','wpshadow'),'severity'=>'high','threat_level'=>90,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/privilege-escalation-not-prevented');}return null;}}
