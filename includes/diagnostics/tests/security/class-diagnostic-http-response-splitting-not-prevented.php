<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_HTTP_Response_Splitting_Not_Prevented extends Diagnostic_Base{protected static $slug='http-response-splitting-not-prevented';protected static $title='HTTP Response Splitting Not Prevented';protected static $description='Checks response splitting';protected static $family='security';public static function check(){if(!has_filter('init','prevent_response_splitting')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('HTTP response splitting not prevented. Validate all header values and remove CRLF characters.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/http-response-splitting-not-prevented');}return null;}}
