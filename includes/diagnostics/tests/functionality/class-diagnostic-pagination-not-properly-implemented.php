<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Pagination_Not_Properly_Implemented extends Diagnostic_Base{protected static $slug='pagination-not-properly-implemented';protected static $title='Pagination Not Properly Implemented';protected static $description='Checks pagination';protected static $family='functionality';public static function check(){if(!has_filter('init','implement_safe_pagination')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Pagination not properly implemented. Prevent offset overflow and implement cursor-based pagination for large datasets.','wpshadow'),'severity'=>'low','threat_level'=>15,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/pagination-not-properly-implemented');}return null;}}
