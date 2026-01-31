<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_MIME_Type_Sniffing_Not_Prevented extends Diagnostic_Base{protected static $slug='mime-type-sniffing-not-prevented';protected static $title='MIME Type Sniffing Not Prevented';protected static $description='Checks MIME sniffing';protected static $family='security';public static function check(){if(!has_filter('init','prevent_mime_sniffing')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('MIME type sniffing not prevented. Set X-Content-Type-Options: nosniff header to prevent browser MIME sniffing.','wpshadow'),'severity'=>'medium','threat_level'=>50,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/mime-type-sniffing-not-prevented');}return null;}}
