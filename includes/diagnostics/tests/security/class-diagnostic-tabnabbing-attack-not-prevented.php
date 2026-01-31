<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Tabnabbing_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='tabnabbing-attack-not-prevented';protected static $title='Tabnabbing Attack Not Prevented';protected static $description='Checks tabnabbing';protected static $family='security';public static function check(){if(!has_filter('init','prevent_tabnabbing')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Tabnabbing attack not prevented. Set rel="noopener noreferrer" on external links to prevent window.opener access.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/tabnabbing-attack-not-prevented');}return null;}}
