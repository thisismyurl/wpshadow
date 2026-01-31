<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_NoSQL_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='nosql-injection-not-prevented';protected static $title='NoSQL Injection Not Prevented';protected static $description='Checks NoSQL injection';protected static $family='security';public static function check(){if(!has_filter('init','sanitize_nosql_queries')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('NoSQL injection not prevented. Parameterize all NoSQL queries and validate/sanitize query operators like $where and $regex.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/nosql-injection-not-prevented');}return null;}}
