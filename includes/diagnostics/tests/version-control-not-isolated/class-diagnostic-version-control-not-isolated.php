<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Version_Control_Not_Isolated extends Diagnostic_Base{protected static $slug='version-control-not-isolated';protected static $title='Version Control Not Isolated';protected static $description='Checks version control isolation';protected static $family='admin';public static function check(){if(!file_exists(ABSPATH.'.gitignore')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Version control not isolated. Exclude sensitive files (.env, config, secrets) from version control.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/version-control-not-isolated');}return null;}}
