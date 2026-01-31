<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Unicode_Normalization_Not_Applied extends Diagnostic_Base{protected static $slug='unicode-normalization-not-applied';protected static $title='Unicode Normalization Not Applied';protected static $description='Checks unicode normalization';protected static $family='security';public static function check(){if(!has_filter('init','normalize_unicode_input')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Unicode normalization not applied. Use unicode normalization (NFC/NFD) to prevent homograph attacks.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/unicode-normalization-not-applied');}return null;}}
