<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Dependency_Confusion_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='dependency-confusion-attack-not-prevented';protected static $title='Dependency Confusion Attack Not Prevented';protected static $description='Checks dependency confusion';protected static $family='security';public static function check(){if(!has_filter('init','prevent_dependency_confusion')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Dependency confusion attack not prevented. Use composer repository config and verify package integrity.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/dependency-confusion-attack-not-prevented');}return null;}}
