<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Graceful_Degradation_Not_Tested extends Diagnostic_Base{protected static $slug='graceful-degradation-not-tested';protected static $title='Graceful Degradation Not Tested';protected static $description='Checks graceful degradation';protected static $family='functionality';public static function check(){if(!get_option('graceful_degradation_tested')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Graceful degradation not tested. Test with JavaScript disabled, old browsers, and slow connections to ensure core functionality works.','wpshadow'),'severity'=>'low','threat_level'=>20,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/graceful-degradation-not-tested');}return null;}}
