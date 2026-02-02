<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Cookie_Consent_Not_Implemented extends Diagnostic_Base{protected static $slug='cookie-consent-not-implemented';protected static $title='Cookie Consent Not Implemented';protected static $description='Checks cookie consent';protected static $family='privacy';public static function check(){if(!get_option('cookie_consent_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Cookie consent not implemented. Show consent banner and only load tracking after explicit user consent (GDPR/CCPA).','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/cookie-consent-not-implemented');}return null;}}
