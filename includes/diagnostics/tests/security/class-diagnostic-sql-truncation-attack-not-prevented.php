<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_SQL_Truncation_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='sql-truncation-attack-not-prevented';protected static $title='SQL Truncation Attack Not Prevented';protected static $description='Checks SQL truncation';protected static $family='security';public static function check(){if(!has_filter('init','prevent_sql_truncation')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('SQL truncation attack not prevented. Use parameterized queries and implement strict field length validation.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/sql-truncation-attack-not-prevented');}return null;}}
