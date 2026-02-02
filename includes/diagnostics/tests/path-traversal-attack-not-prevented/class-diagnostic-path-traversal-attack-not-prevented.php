<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Path_Traversal_Attack_Not_Prevented extends Diagnostic_Base{protected static $slug='path-traversal-attack-not-prevented';protected static $title='Path Traversal Attack Not Prevented';protected static $description='Checks path traversal';protected static $family='security';public static function check(){if(!has_filter('init','validate_file_paths')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Path traversal attack not prevented. Use realpath() and restrict file operations to allowed directories.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/path-traversal-attack-not-prevented');}return null;}}
