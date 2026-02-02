<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_No_SQL_Injection_Not_Prevented extends Diagnostic_Base{protected static $slug='no-sql-injection-not-prevented';protected static $title='NoSQL Injection Not Prevented';protected static $description='Checks NoSQL injection prevention';protected static $family='security';public static function check(){if(!has_filter('init','validate_nosql_queries')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('NoSQL injection not prevented. Use schema validation and parameterized queries for all NoSQL operations.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/no-sql-injection-not-prevented');}return null;}}
