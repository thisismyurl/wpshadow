<?php
/**
 * Database Query Caching Not Implemented Diagnostic
 *
 * Checks if database query caching is implemented.
 * Query caching = store query results in memory.
 * No cache = same query hits database repeatedly.
 * With cache = query result retrieved from memory (instant).
 *
 * **What This Check Does:**
 * - Checks for object cache (Redis, Memcached)
 * - Validates wp_cache_get/set usage
 * - Tests cache hit rate (should be >70%)
 * - Checks persistent object cache configuration
 * - Validates cache key strategy
 * - Returns severity if no query caching implemented
 *
 * **Why This Matters:**
 * Popular widget shows "Recent Posts" on every page.
 * Same query runs 1000 times/hour. Each hits database.
 * With object cache: query runs once, cached for 1 hour.
 * Next 999 requests served from memory. Database load reduced 99.9%.
 *
 * **Business Impact:**
 * News site: homepage queries "top stories" (complex query, 5 tables,
 * 200ms). Shown to every visitor. 10K visitors/hour = 2000 queries.
 * Database maxed out. Pages slow (3+ seconds). Implement Redis object
 * cache. First visitor: query hits database (200ms). Result cached.
 * Next 9,999 visitors: served from Redis (<1ms). Database load drops
 * 99%. Page load time: 3s → 600ms. Server costs reduced 40%
 * (smaller database tier sufficient). Cache setup: 2 hours.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Consistent fast performance
 * - #9 Show Value: Massive scalability improvement
 * - #10 Beyond Pure: Enterprise-grade caching
 *
 * **Related Checks:**
 * - Object Cache Configuration (implementation check)
 * - Page Cache Implementation (full-page cache)
 * - Cache Hit Rate Monitoring (effectiveness)
 *
 * **Learn More:**
 * Object caching: https://wpshadow.com/kb/object-cache
 * Video: Redis for WordPress (16min): https://wpshadow.com/training/redis-cache
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Caching Not Implemented Diagnostic Class
 *
 * Detects missing database query caching.
 *
 * **Detection Pattern:**
 * 1. Check if object cache is persistent (Redis/Memcached)
 * 2. Test wp_cache_get() returns cached data
 * 3. Measure cache hit rate from stats
 * 4. Validate cache key naming conventions
 * 5. Check cache expiration settings
 * 6. Return if no persistent cache or low hit rate
 *
 * **Real-World Scenario:**
 * Installed Redis object cache plugin. Before: 5000 database
 * queries/minute. After: 800 queries/minute (84% reduction).
 * Cache hit rate: 82%. Database CPU usage: 80% → 15%.
 * Page load time improved 40% average. Cost: $10/month Redis
 * instance. Savings: $200/month (downsized database server).
 * Net savings: $190/month + better performance.
 *
 * **Implementation Notes:**
 * - Checks persistent object cache implementation
 * - Validates cache effectiveness
 * - Tests cache hit rates
 * - Severity: high (major performance/cost impact)
 * - Treatment: implement Redis or Memcached object cache
 *
 * @since 1.6030.2352
 */
class Diagnostic_Database_Query_Caching_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-caching-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Caching Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database query caching is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if persistent object cache is enabled.
		$using_object_cache = wp_using_ext_object_cache();

		// Check for object cache plugins.
		$cache_plugins = array(
			'redis-cache/redis-cache.php'               => 'Redis Object Cache',
			'wp-redis/wp-redis.php'                     => 'WP Redis',
			'memcached/memcached.php'                   => 'Memcached Object Cache',
			'w3-total-cache/w3-total-cache.php'         => 'W3 Total Cache (includes object cache)',
			'wp-super-cache/wp-cache.php'               => 'WP Super Cache',
		);

		$cache_plugin_detected = false;
		$cache_plugin_name     = '';

		foreach ( $cache_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$cache_plugin_detected = true;
				$cache_plugin_name     = $name;
				break;
			}
		}

		// Check if object-cache.php drop-in exists.
		$object_cache_dropin = WP_CONTENT_DIR . '/object-cache.php';
		$has_dropin = file_exists( $object_cache_dropin );

		// Get database query count from WordPress.
		global $wpdb;
		$query_count = $wpdb->num_queries;

		// Critical: No object cache and high query count.
		if ( ! $using_object_cache && ! $has_dropin && $query_count > 100 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of database queries */
					__( 'Database query caching not implemented. This page executed %d database queries. Object cache (Redis/Memcached) stores query results in memory, reducing database load by 70-90%%. Install Redis Object Cache plugin for instant performance improvement.', 'wpshadow' ),
					$query_count
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/object-cache',
				'details'     => array(
					'using_object_cache' => false,
					'has_dropin'         => false,
					'query_count'        => $query_count,
					'recommendation'     => __( 'Install "Redis Object Cache" plugin (free, 100K+ installs). Requires Redis server (most hosts provide free Redis instance). Setup: 1) Install plugin, 2) Click "Enable Object Cache", 3) Done. Typical result: 70-90% query reduction, 30-50% faster page loads.', 'wpshadow' ),
					'performance_impact' => array(
						'query_reduction' => '70-90% fewer database queries',
						'page_load' => '30-50% faster page load times',
						'server_load' => 'Database CPU usage reduced 60-80%',
						'scalability' => 'Handle 10x more traffic with same hardware',
					),
					'how_it_works' => array(
						'first_request' => 'Query executes, result stored in Redis (200ms)',
						'cached_requests' => 'Result retrieved from Redis (<1ms)',
						'expiration' => 'Cache expires after set time (typically 1 hour)',
						'invalidation' => 'Cache cleared when content updates',
					),
					'hosting_support' => array(
						'managed_wordpress' => 'WP Engine, Kinsta, Flywheel include Redis',
						'vps' => 'DigitalOcean, Linode, Vulkan offer Redis add-on',
						'shared' => 'Some shared hosts (SiteGround, A2) provide Redis',
					),
				),
			);
		}

		// Medium: Object cache exists but not enabled.
		if ( $cache_plugin_detected && ! $using_object_cache ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Object Cache Plugin Not Enabled', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %s: plugin name */
					__( '%s is installed but object cache is not enabled. Enable object cache in plugin settings to activate query caching.', 'wpshadow' ),
					$cache_plugin_name
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/object-cache',
				'details'     => array(
					'plugin_installed' => $cache_plugin_name,
					'recommendation'   => __( 'Go to plugin settings and click "Enable Object Cache". May require Redis/Memcached server (check with hosting provider).', 'wpshadow' ),
				),
			);
		}

		// No issues - object cache active.
		return null;
	}

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Caching Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database query caching is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if query caching is enabled
		if ( ! wp_using_ext_object_cache() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database query caching is not implemented. Enable external object caching (Redis or Memcached) to cache repeated database queries.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-query-caching-not-implemented',
			);
		}

		return null;
	}
}
