<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Query_Performance_Not_Analyzed extends Diagnostic_Base{protected static $slug='query-performance-not-analyzed';protected static $title='Query Performance Not Analyzed';protected static $description='Checks query performance';protected static $family='performance';public static function check(){if(!has_filter('init','log_slow_queries')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Query performance not analyzed. Enable slow query log and use EXPLAIN to identify missing indexes and optimize queries.','wpshadow'),'severity'=>'high','threat_level'=>60,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/query-performance-not-analyzed');}return null;}}
