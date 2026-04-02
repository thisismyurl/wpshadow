<?php
/**
 * Object Cache Configuration Diagnostic
 *
 * Checks if persistent object caching is configured properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Object Cache Configuration Diagnostic Class
 *
 * Verifies persistent object cache (Redis/Memcached) is properly configured.
 * Persistent object caching dramatically reduces database load.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Object_Cache_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'object-cache-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Object Cache Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks persistent object cache configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for persistent object cache and proper configuration.
	 * Without persistent cache, all cached data is lost between requests.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_object_cache;
		
		// Check if using default WordPress object cache (non-persistent)
		if ( ! is_object( $wp_object_cache ) ) {
			return array(
				'id'           => 'object-cache-not-available',
				'title'        => __( 'Object Cache Not Available', 'wpshadow' ),
				'description'  => __( 'Object cache is not initialized. This is a core WordPress issue that should not occur.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/object-cache-troubleshooting',
			);
		}
		
		$cache_class = get_class( $wp_object_cache );
		
		// Default WP_Object_Cache is non-persistent
		if ( 'WP_Object_Cache' === $cache_class ) {
			return array(
				'id'           => 'persistent-cache-not-enabled',
				'title'        => __( 'Persistent Object Cache Not Enabled', 'wpshadow' ),
				'description'  => __( 'Adding a persistent cache system helps your site remember things between page loads (like keeping food in a refrigerator instead of buying it fresh each time). This can reduce database queries by 30-70%. Popular options include Redis or Memcached with a WordPress plugin.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-persistent-cache',
				'meta'         => array(
					'current_cache'         => $cache_class,
					'persistent'            => false,
					'recommended_solutions' => array(
						'Redis + Redis Object Cache plugin',
						'Memcached + Memcached Object Cache plugin',
					),
					'performance_impact'    => '30-70% reduction in database queries',
				),
			);
		}
		
		// Persistent cache is enabled, check configuration
		$issues = array();
		$score  = 0;
		
		// Check Redis configuration
		if ( strpos( $cache_class, 'Redis' ) !== false ) {
			// Check if Redis is actually connected
			if ( method_exists( $wp_object_cache, 'redis_status' ) ) {
				$status = $wp_object_cache->redis_status();
				
				if ( empty( $status ) || ! isset( $status['status'] ) || 'connected' !== $status['status'] ) {
					$issues[] = __( 'Redis object cache plugin active but not connected', 'wpshadow' );
					$score += 40;
				}
			}
			
			// Check for Redis-specific optimizations
			if ( ! defined( 'WP_REDIS_MAXTTL' ) ) {
				$issues[] = __( 'WP_REDIS_MAXTTL not configured (recommended: 86400)', 'wpshadow' );
				$score += 10;
			}
			
			if ( ! defined( 'WP_REDIS_DISABLED' ) && defined( 'WP_REDIS_DISABLED' ) && WP_REDIS_DISABLED ) {
				$issues[] = __( 'Redis cache is disabled via WP_REDIS_DISABLED constant', 'wpshadow' );
				$score += 50;
			}
		}
		
		// Check Memcached configuration
		if ( strpos( $cache_class, 'Memcache' ) !== false ) {
			if ( ! class_exists( 'Memcached' ) && ! class_exists( 'Memcache' ) ) {
				$issues[] = __( 'Memcached plugin active but PHP extension not loaded', 'wpshadow' );
				$score += 40;
			}
		}
		
		// Check for object-cache.php drop-in
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		if ( ! file_exists( $object_cache_file ) ) {
			$issues[] = __( 'object-cache.php drop-in missing from wp-content', 'wpshadow' );
			$score += 30;
		}
		
		// Check cache groups configuration
		if ( method_exists( $wp_object_cache, 'add_global_groups' ) ) {
			// Check if global groups are configured
			$global_groups = wp_cache_get_global_groups();
			if ( empty( $global_groups ) ) {
				$issues[] = __( 'No global cache groups configured for multisite', 'wpshadow' );
				$score += 15;
			}
		}
		
		// If issues found with persistent cache
		if ( ! empty( $issues ) ) {
			$severity = 'medium';
			if ( $score > 30 ) {
				$severity = 'high';
			}
			
			return array(
				'id'           => 'object-cache-misconfigured',
				'title'        => __( 'Object Cache Misconfigured', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of configuration issues */
					__( 'Persistent object cache is enabled but has configuration issues: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => $score,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/object-cache-configuration',
				'meta'         => array(
					'cache_class'    => $cache_class,
					'persistent'     => true,
					'issues_found'   => count( $issues ),
					'drop_in_exists' => file_exists( $object_cache_file ),
				),
			);
		}
		
		// Object cache is properly configured
		return null;
	}
}
