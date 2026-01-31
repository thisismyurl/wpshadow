<?php
/**
 * Gridpane Redis Object Cache Diagnostic
 *
 * Gridpane Redis Object Cache needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gridpane Redis Object Cache Diagnostic Class
 *
 * @since 1.1029.0000
 */
class Diagnostic_GridpaneRedisObjectCache extends Diagnostic_Base {

	protected static $slug = 'gridpane-redis-object-cache';
	protected static $title = 'Gridpane Redis Object Cache';
	protected static $description = 'Gridpane Redis Object Cache needs attention';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		// Check 1: Redis extension loaded
		if ( ! extension_loaded( 'redis' ) ) {
			$issues[] = 'Redis extension not loaded';
		}

		// Check 2: Object cache enabled
		$object_cache = get_option( 'gridpane_redis_enabled', false );
		if ( ! $object_cache ) {
			$issues[] = 'Redis object cache disabled';
		}

		// Check 3: Redis connection configured
		$redis_host = get_option( 'gridpane_redis_host', '' );
		if ( empty( $redis_host ) ) {
			$issues[] = 'Redis host not configured';
		}

		// Check 4: Persistent connections enabled
		$persistent = get_option( 'gridpane_redis_persistent', false );
		if ( ! $persistent ) {
			$issues[] = 'Persistent connections disabled';
		}

		// Check 5: Key prefix configured
		$key_prefix = get_option( 'gridpane_redis_prefix', '' );
		if ( empty( $key_prefix ) ) {
			$issues[] = 'Redis key prefix not set';
		}

		// Check 6: Max TTL configured
		$max_ttl = get_option( 'gridpane_redis_max_ttl', 0 );
		if ( $max_ttl <= 0 ) {
			$issues[] = 'Max TTL not configured';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GridPane Redis object cache issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gridpane-redis-object-cache',
			);
		}

		return null;
	}
		}
		return null;
	}
}
