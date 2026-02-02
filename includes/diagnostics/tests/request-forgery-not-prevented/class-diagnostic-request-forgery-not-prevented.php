<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Request_Forgery_Not_Prevented extends Diagnostic_Base{protected static $slug='request-forgery-not-prevented';protected static $title='Request Forgery Not Prevented';protected static $description='Checks request forgery';protected static $family='security';public static function check(){if(!has_filter('init','validate_request_origin')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Request forgery not prevented. Implement CSRF tokens and validate request origins for state-changing operations.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/request-forgery-not-prevented');}return null;}}
