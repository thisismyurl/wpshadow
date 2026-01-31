<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Race_Condition_Not_Prevented extends Diagnostic_Base{protected static $slug='race-condition-not-prevented';protected static $title='Race Condition Not Prevented';protected static $description='Checks race conditions';protected static $family='security';public static function check(){if(!has_filter('init','prevent_race_conditions')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Race condition not prevented. Use atomic database operations and locks for critical sections.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/race-condition-not-prevented');}return null;}}
