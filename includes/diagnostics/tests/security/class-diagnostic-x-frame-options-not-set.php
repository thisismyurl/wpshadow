<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_X_Frame_Options_Not_Set extends Diagnostic_Base{protected static $slug='x-frame-options-not-set';protected static $title='X-Frame-Options Not Set';protected static $description='Checks X-Frame-Options';protected static $family='security';public static function check(){if(!has_filter('init','set_x_frame_options')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('X-Frame-Options header not set. Set to DENY or SAMEORIGIN to prevent clickjacking attacks.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/x-frame-options-not-set');}return null;}}
