<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Open_Redirect_Not_Prevented extends Diagnostic_Base{protected static $slug='open-redirect-not-prevented';protected static $title='Open Redirect Not Prevented';protected static $description='Checks open redirect';protected static $family='security';public static function check(){if(!has_filter('init','prevent_open_redirects')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Open redirect not prevented. Validate redirect URLs against a whitelist before redirecting users.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/open-redirect-not-prevented');}return null;}}
