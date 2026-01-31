<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Webhook_Validation_Not_Implemented extends Diagnostic_Base{protected static $slug='webhook-validation-not-implemented';protected static $title='Webhook Validation Not Implemented';protected static $description='Checks webhook validation';protected static $family='functionality';public static function check(){if(!has_filter('init','validate_webhooks')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Webhook validation not implemented. Always verify webhook signatures using HMAC to prevent spoofed requests.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/webhook-validation-not-implemented');}return null;}}
