<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Network_Request_Batching_Not_Optimized extends Diagnostic_Base{protected static $slug='network-request-batching-not-optimized';protected static $title='Network Request Batching Not Optimized';protected static $description='Checks if network requests are batched';protected static $family='performance';public static function check(){if(!has_filter('wp_head','batch_network_requests')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Network request batching not optimized. Combine multiple API calls into single batched requests to reduce round trips and improve performance.','wpshadow'),'severity'=>'low','threat_level'=>20,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/network-request-batching-not-optimized');}return null;}}
