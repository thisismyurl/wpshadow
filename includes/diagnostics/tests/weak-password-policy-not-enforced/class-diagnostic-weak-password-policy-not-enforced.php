<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Weak_Password_Policy_Not_Enforced extends Diagnostic_Base{protected static $slug='weak-password-policy-not-enforced';protected static $title='Weak Password Policy Not Enforced';protected static $description='Checks password policy';protected static $family='security';public static function check(){if(!get_option('strong_password_policy_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Weak password policy not enforced. Require minimum 12 characters, complexity, and password history.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/weak-password-policy-not-enforced');}return null;}}
