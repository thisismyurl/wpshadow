<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Fallback_Authentication_Not_Available extends Diagnostic_Base{protected static $slug='fallback-authentication-not-available';protected static $title='Fallback Authentication Not Available';protected static $description='Checks fallback auth';protected static $family='security';public static function check(){if(!has_filter('init','provide_fallback_authentication')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Fallback authentication not available. Provide backup authentication method when primary service is unavailable.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/fallback-authentication-not-available');}return null;}}
