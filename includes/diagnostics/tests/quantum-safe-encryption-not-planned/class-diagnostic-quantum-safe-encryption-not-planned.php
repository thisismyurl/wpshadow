<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Quantum_Safe_Encryption_Not_Planned extends Diagnostic_Base{protected static $slug='quantum-safe-encryption-not-planned';protected static $title='Quantum Safe Encryption Not Planned';protected static $description='Checks quantum safe encryption planning';protected static $family='security';public static function check(){if(!get_option('quantum_safe_plan_date')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Quantum safe encryption not planned. Begin planning post-quantum cryptography migration now before quantum computers become viable threats to current encryption.','wpshadow'),'severity'=>'low','threat_level'=>5,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/quantum-safe-encryption-not-planned');}return null;}}
