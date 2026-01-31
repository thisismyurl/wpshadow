<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Key_Management_Not_Implemented extends Diagnostic_Base{protected static $slug='key-management-not-implemented';protected static $title='Key Management Not Implemented';protected static $description='Checks key management';protected static $family='security';public static function check(){if(!has_filter('init','manage_encryption_keys')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Key management not implemented. Rotate encryption keys regularly and store them securely outside code.','wpshadow'),'severity'=>'high','threat_level'=>85,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/key-management-not-implemented');}return null;}}
