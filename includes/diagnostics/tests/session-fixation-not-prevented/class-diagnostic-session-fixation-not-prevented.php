<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Session_Fixation_Not_Prevented extends Diagnostic_Base{protected static $slug='session-fixation-not-prevented';protected static $title='Session Fixation Not Prevented';protected static $description='Checks session fixation';protected static $family='security';public static function check(){if(!has_filter('init','regenerate_session_id')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Session fixation not prevented. Regenerate session IDs on login and use secure, HttpOnly cookie flags.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/session-fixation-not-prevented');}return null;}}
