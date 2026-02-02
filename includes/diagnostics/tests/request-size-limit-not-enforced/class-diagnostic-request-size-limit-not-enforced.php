<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Request_Size_Limit_Not_Enforced extends Diagnostic_Base{protected static $slug='request-size-limit-not-enforced';protected static $title='Request Size Limit Not Enforced';protected static $description='Checks request limits';protected static $family='security';public static function check(){if(!has_filter('init','enforce_request_size_limit')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Request size limit not enforced. Set max upload size and POST size limits to prevent resource exhaustion.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/request-size-limit-not-enforced');}return null;}}
