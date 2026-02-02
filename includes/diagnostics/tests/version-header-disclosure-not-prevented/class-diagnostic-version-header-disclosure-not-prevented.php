<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Version_Header_Disclosure_Not_Prevented extends Diagnostic_Base{protected static $slug='version-header-disclosure-not-prevented';protected static $title='Version Header Disclosure Not Prevented';protected static $description='Checks version disclosure';protected static $family='security';public static function check(){if(!has_filter('init','remove_version_headers')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Version header disclosure not prevented. Remove X-Powered-By and Server version headers to reduce information exposure.','wpshadow'),'severity'=>'medium','threat_level'=>25,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/version-header-disclosure-not-prevented');}return null;}}
