<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_XXE_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='xxe-attack-not-prevented';protected static $title='XXE Attack Not Prevented';protected static $description='Checks XXE attacks';protected static $family='security';public static function check(){if(!has_filter('init','prevent_xxe_attacks')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('XXE attack not prevented. Disable XML external entities and document type declarations in all XML/PDF parsing.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/xxe-attack-not-prevented');}return null;}}
