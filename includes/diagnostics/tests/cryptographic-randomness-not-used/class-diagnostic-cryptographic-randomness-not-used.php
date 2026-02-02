<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Cryptographic_Randomness_Not_Used extends Diagnostic_Base{protected static $slug='cryptographic-randomness-not-used';protected static $title='Cryptographic Randomness Not Used';protected static $description='Checks random generation';protected static $family='security';public static function check(){if(!has_filter('init','use_cryptographic_random')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Cryptographic randomness not used. Use random_bytes() or wp_generate_password() for security tokens, never mt_rand().','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/cryptographic-randomness-not-used');}return null;}}
