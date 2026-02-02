<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Zero_Configuration_Deployment_Not_Available extends Diagnostic_Base{protected static $slug='zero-configuration-deployment-not-available';protected static $title='Zero Configuration Deployment Not Available';protected static $description='Checks zero-config deployment';protected static $family='admin';public static function check(){if(!get_option('zero_config_deployment_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Zero-config deployment not available. Enable one-click deployments with auto-configuration to reduce operational overhead and deployment time.','wpshadow'),'severity'=>'low','threat_level'=>10,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/zero-configuration-deployment-not-available');}return null;}}
