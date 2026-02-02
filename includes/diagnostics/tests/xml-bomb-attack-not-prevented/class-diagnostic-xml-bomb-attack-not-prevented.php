<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_XML_Bomb_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='xml-bomb-attack-not-prevented';protected static $title='XML Bomb Attack Not Prevented';protected static $description='Checks XML bomb prevention';protected static $family='security';public static function check(){if(!has_filter('init','prevent_xml_bombs')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('XML bomb attack not prevented. Disable XML external entities (XXE) and limit entity expansion in XML parsing.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/xml-bomb-attack-not-prevented');}return null;}}
