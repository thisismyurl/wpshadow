<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Email_Validation_Not_Comprehensive extends Diagnostic_Base{protected static $slug='email-validation-not-comprehensive';protected static $title='Email Validation Not Comprehensive';protected static $description='Checks email validation';protected static $family='functionality';public static function check(){if(!has_filter('init','validate_email_thoroughly')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Email validation not comprehensive. Implement format validation, DNS checks, and SMTP verification.','wpshadow'),'severity'=>'medium','threat_level'=>30,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/email-validation-not-comprehensive');}return null;}}
