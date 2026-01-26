<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Object_Count_Monitoring extends Diagnostic_Base {
	protected static $slug = 'object-count-monitoring';
	protected static $title = 'High Object Count';
	protected static $description = 'Detects unusually large object counts';
	protected static $family = 'monitoring';
	public static function check() {
		global $wp_object_cache;
		if ( empty( $wp_object_cache ) ) { return null; }
		$cache_stats = wp_cache_get_stats();
		if ( ! empty( $cache_stats ) && isset( $cache_stats['total_requests'] ) && $cache_stats['total_requests'] > 10000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Unusually high object cache requests (%d). This may indicate inefficient queries. Consider optimizing database queries and using persistent caching.', 'wpshadow' ), $cache_stats['total_requests'] ),
				'severity' => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/object-count-monitoring',
				'meta' => array( 'total_requests' => $cache_stats['total_requests'] ?? 0 ),
			);
		}
		return null;
	}
}
