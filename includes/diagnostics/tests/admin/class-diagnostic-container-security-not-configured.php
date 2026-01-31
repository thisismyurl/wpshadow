<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Container_Security_Not_Configured extends Diagnostic_Base{protected static $slug='container-security-not-configured';protected static $title='Container Security Not Configured';protected static $description='Checks container security';protected static $family='admin';public static function check(){if(!has_filter('init','verify_container_security')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Container security not configured. Scan container images for vulnerabilities and enforce resource limits.','wpshadow'),'severity'=>'medium','threat_level'=>45,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/container-security-not-configured');}return null;}}
