<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Multisite_Isolation_Not_Enforced extends Diagnostic_Base{protected static $slug='multisite-isolation-not-enforced';protected static $title='Multisite Isolation Not Enforced';protected static $description='Checks multisite isolation';protected static $family='admin';public static function check(){if(is_multisite()&&!has_filter('init','enforce_multisite_isolation')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Multisite isolation not enforced. Prevent cross-site data access and enforce role separation.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/multisite-isolation-not-enforced');}return null;}}
