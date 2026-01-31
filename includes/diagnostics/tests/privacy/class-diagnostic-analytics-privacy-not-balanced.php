<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Analytics_Privacy_Not_Balanced extends Diagnostic_Base{protected static $slug='analytics-privacy-not-balanced';protected static $title='Analytics Privacy Not Balanced';protected static $description='Checks analytics balance';protected static $family='privacy';public static function check(){if(!get_option('analytics_privacy_mode')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Analytics privacy not balanced. Use privacy-first analytics (Plausible, Fathom) instead of Google Analytics.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/analytics-privacy-not-balanced');}return null;}}
