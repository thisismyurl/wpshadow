<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Progressive_Image_Loading_Not_Implemented extends Diagnostic_Base{protected static $slug='progressive-image-loading-not-implemented';protected static $title='Progressive Image Loading Not Implemented';protected static $description='Checks progressive image loading';protected static $family='performance';public static function check(){if(!has_filter('wp_head','enable_progressive_images')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Progressive image loading not implemented. Load low-quality image placeholder while high-quality version loads for improved perceived performance.','wpshadow'),'severity'=>'low','threat_level'=>15,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/progressive-image-loading-not-implemented');}return null;}}
