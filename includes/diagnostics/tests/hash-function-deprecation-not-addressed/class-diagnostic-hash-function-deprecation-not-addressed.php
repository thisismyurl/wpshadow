<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Hash_Function_Deprecation_Not_Addressed extends Diagnostic_Base{protected static $slug='hash-function-deprecation-not-addressed';protected static $title='Hash Function Deprecation Not Addressed';protected static $description='Checks hash function security';protected static $family='security';public static function check(){if(!has_filter('init','use_argon2_hashing')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Hash function deprecation not addressed. Use password_hash() with argon2 instead of MD5/SHA1 for secure password hashing.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/hash-function-deprecation-not-addressed');}return null;}}
