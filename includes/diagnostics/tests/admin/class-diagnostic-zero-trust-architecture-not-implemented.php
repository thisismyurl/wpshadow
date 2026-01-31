<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Zero_Trust_Architecture_Not_Implemented extends Diagnostic_Base{protected static $slug='zero-trust-architecture-not-implemented';protected static $title='Zero Trust Architecture Not Implemented';protected static $description='Checks zero trust';protected static $family='admin';public static function check(){if(!has_filter('init','implement_zero_trust')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Zero trust architecture not implemented. Verify every request, regardless of origin, with authentication and authorization.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/zero-trust-architecture-not-implemented');}return null;}}
