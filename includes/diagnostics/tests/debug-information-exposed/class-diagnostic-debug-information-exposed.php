<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Debug_Information_Exposed extends Diagnostic_Base{protected static $slug='debug-information-exposed';protected static $title='Debug Information Exposed';protected static $description='Checks debug exposure';protected static $family='security';public static function check(){if(defined('WP_DEBUG')&&WP_DEBUG){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Debug information exposed in production. Set WP_DEBUG to false and log errors to file, not screen.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/debug-information-exposed');}return null;}}
