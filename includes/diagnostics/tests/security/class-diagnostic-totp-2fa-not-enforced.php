<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_TOTP_2FA_Not_Enforced extends Diagnostic_Base{protected static $slug='totp-2fa-not-enforced';protected static $title='TOTP 2FA Not Enforced';protected static $description='Checks TOTP 2FA';protected static $family='security';public static function check(){if(!get_option('totp_2fa_enforced')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('TOTP 2FA not enforced for admin accounts. Use libraries like PHPGangsta_GoogleAuthenticator to enable time-based one-time passwords.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/totp-2fa-not-enforced');}return null;}}
