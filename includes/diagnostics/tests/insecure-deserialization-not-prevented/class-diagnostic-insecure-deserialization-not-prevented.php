<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Insecure_Deserialization_Not_Prevented extends Diagnostic_Base{protected static $slug='insecure-deserialization-not-prevented';protected static $title='Insecure Deserialization Not Prevented';protected static $description='Checks deserialization';protected static $family='security';public static function check(){if(!has_filter('init','validate_deserialization')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Insecure deserialization not prevented. Never unserialize() data from user input or untrusted sources.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/insecure-deserialization-not-prevented');}return null;}}
