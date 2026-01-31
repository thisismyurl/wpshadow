<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Design_System_Not_Documented extends Diagnostic_Base{protected static $slug='design-system-not-documented';protected static $title='Design System Not Documented';protected static $description='Checks design system documentation';protected static $family='admin';public static function check(){if(!get_option('design_system_documented')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Design system not documented. Create comprehensive design documentation covering colors, typography, components for consistency.','wpshadow'),'severity'=>'low','threat_level'=>15,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/design-system-not-documented');}return null;}}
