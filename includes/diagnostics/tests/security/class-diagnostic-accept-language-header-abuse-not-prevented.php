<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Accept_Language_Header_Abuse_Not_Prevented extends Diagnostic_Base{protected static $slug='accept-language-header-abuse-not-prevented';protected static $title='Accept-Language Header Abuse Not Prevented';protected static $description='Checks Accept-Language abuse';protected static $family='security';public static function check(){if(!has_filter('init','validate_accept_language')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Accept-Language header abuse not prevented. Validate language codes and prevent header injection attacks.','wpshadow'),'severity'=>'medium','threat_level'=>30,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/accept-language-header-abuse-not-prevented');}return null;}}
