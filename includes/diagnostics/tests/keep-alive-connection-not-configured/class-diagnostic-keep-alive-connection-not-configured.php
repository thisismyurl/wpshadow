<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Keep_Alive_Connection_Not_Configured extends Diagnostic_Base{protected static $slug='keep-alive-connection-not-configured';protected static $title='Keep-Alive Connection Not Configured';protected static $description='Checks keep-alive';protected static $family='performance';public static function check(){if(!has_filter('init','enable_keep_alive')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Keep-Alive connection not configured. Enable persistent connections to reduce connection overhead.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/keep-alive-connection-not-configured');}return null;}}
