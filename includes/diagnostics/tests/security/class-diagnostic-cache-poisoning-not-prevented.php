<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Cache_Poisoning_Not_Prevented extends Diagnostic_Base{protected static $slug='cache-poisoning-not-prevented';protected static $title='Cache Poisoning Not Prevented';protected static $description='Checks cache poisoning';protected static $family='security';public static function check(){if(!has_filter('init','prevent_cache_poisoning')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Cache poisoning not prevented. Validate cache keys and use cache key namespacing to prevent collision attacks.','wpshadow'),'severity'=>'high','threat_level'=>65,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/cache-poisoning-not-prevented');}return null;}}
