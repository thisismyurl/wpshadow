<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Accelerated_Mobile_Pages_Not_Implemented extends Diagnostic_Base{protected static $slug='accelerated-mobile-pages-not-implemented';protected static $title='Accelerated Mobile Pages Not Implemented';protected static $description='Checks AMP implementation';protected static $family='performance';public static function check(){if(!is_plugin_active('amp/amp.php')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('AMP not implemented. Consider implementing Google AMP pages for mobile traffic to achieve lightning-fast load times and improved SEO.','wpshadow'),'severity'=>'low','threat_level'=>15,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/accelerated-mobile-pages-not-implemented');}return null;}}
