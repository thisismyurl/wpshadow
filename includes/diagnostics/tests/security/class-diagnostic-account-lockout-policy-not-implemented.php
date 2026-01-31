<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Account_Lockout_Policy_Not_Implemented extends Diagnostic_Base{protected static $slug='account-lockout-policy-not-implemented';protected static $title='Account Lockout Policy Not Implemented';protected static $description='Checks account lockout';protected static $family='security';public static function check(){if(!get_option('account_lockout_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Account lockout policy not implemented. Lock accounts after N failed login attempts to prevent brute force attacks.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/account-lockout-policy-not-implemented');}return null;}}
