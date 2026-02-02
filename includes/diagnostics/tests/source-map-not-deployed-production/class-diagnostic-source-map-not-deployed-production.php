<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Source_Map_Not_Deployed_Production extends Diagnostic_Base{protected static $slug='source-map-not-deployed-production';protected static $title='Source Map Not Deployed Production';protected static $description='Checks source map deployment';protected static $family='security';public static function check(){if(file_exists(ABSPATH.'js/app.js.map')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Source maps deployed to production. Remove .map files from production to prevent source code exposure and reduce file sizes.','wpshadow'),'severity'=>'medium','threat_level'=>35,'auto_fixable'=>true,'kb_link'=>'https://wpshadow.com/kb/source-map-not-deployed-production');}return null;}}
