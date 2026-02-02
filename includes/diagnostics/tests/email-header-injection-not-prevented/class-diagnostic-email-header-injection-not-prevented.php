<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Email_Header_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='email-header-injection-not-prevented';protected static $title='Email Header Injection Not Prevented';protected static $description='Checks email injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_email_headers')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Email header injection not prevented. Validate email addresses and never concatenate user input into email headers.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/email-header-injection-not-prevented');}return null;}}
