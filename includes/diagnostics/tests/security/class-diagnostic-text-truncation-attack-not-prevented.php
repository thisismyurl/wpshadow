<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Text_Truncation_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='text-truncation-attack-not-prevented';protected static $title='Text Truncation Attack Not Prevented';protected static $description='Checks text truncation';protected static $family='security';public static function check(){if(!has_filter('init','prevent_text_truncation')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Text truncation attack not prevented. Validate field lengths consistently across all layers.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/text-truncation-attack-not-prevented');}return null;}}
