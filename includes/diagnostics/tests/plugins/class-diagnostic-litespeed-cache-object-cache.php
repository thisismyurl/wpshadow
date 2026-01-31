<?php
/**
 * Litespeed Cache Object Cache Diagnostic
 *
 * Litespeed Cache Object Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.903.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Object Cache Diagnostic Class
 *
 * @since 1.903.0000
 */
class Diagnostic_LitespeedCacheObjectCache extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-object-cache';
	protected static $title = 'Litespeed Cache Object Cache';
	protected static $description = 'Litespeed Cache Object Cache not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify object cache is enabled
		$object_cache = get_option( 'litespeed.conf.object', 0 );
		if ( ! $object_cache ) {
			$issues[] = 'LiteSpeed object cache not enabled';
		}

		// Check 2: Check for persistent object cache
		if ( ! wp_using_ext_object_cache() && $object_cache ) {
			$issues[] = 'Object cache enabled but not functioning';
		}

		// Check 3: Verify cache method (Redis/Memcached)
		$cache_method = get_option( 'litespeed.conf.object-kind', '' );
		if ( $object_cache && empty( $cache_method ) ) {
			$issues[] = 'Object cache method not configured';
		}

		// Check 4: Check cache connection
		if ( $object_cache && $cache_method ) {
			$cache_host = get_option( 'litespeed.conf.object-host', '' );
			if ( empty( $cache_host ) ) {
				$issues[] = 'Object cache host not configured';
			}
		}

		// Check 5: Verify cache key prefix
		$cache_prefix = get_option( 'litespeed.conf.object-db_id', '' );
		if ( $object_cache && empty( $cache_prefix ) && is_multisite() ) {
			$issues[] = 'Object cache prefix not set for multisite';
		}

		// Check 6: Check for cache statistics tracking
		$cache_stats = get_option( 'litespeed.conf.object-admin', 0 );
		if ( ! $cache_stats ) {
			$issues[] = 'Object cache statistics not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d LiteSpeed Cache object cache issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-object-cache',
			);
		}

		return null;
	}
}
