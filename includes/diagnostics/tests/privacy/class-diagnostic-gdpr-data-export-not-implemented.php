<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_GDPR_Data_Export_Not_Implemented extends Diagnostic_Base{protected static $slug='gdpr-data-export-not-implemented';protected static $title='GDPR Data Export Not Implemented';protected static $description='Checks GDPR export';protected static $family='privacy';public static function check(){if(!has_filter('init','implement_data_export')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('GDPR data export not implemented. Provide users ability to export personal data in standard formats.','wpshadow'),'severity'=>'high','threat_level'=>85,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/gdpr-data-export-not-implemented');}return null;}}
