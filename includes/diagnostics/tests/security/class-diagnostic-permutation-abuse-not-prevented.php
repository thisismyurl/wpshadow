<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Permutation_Abuse_Not_Prevented extends Diagnostic_Base{protected static $slug='permutation-abuse-not-prevented';protected static $title='Permutation Abuse Not Prevented';protected static $description='Checks permutation abuse';protected static $family='security';public static function check(){if(!has_filter('init','prevent_permutation_abuse')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Permutation abuse not prevented. Implement account lockout after failed login attempts to prevent credential stuffing.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/permutation-abuse-not-prevented');}return null;}}
