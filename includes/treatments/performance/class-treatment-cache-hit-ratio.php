<?php
/**
 * Cache Hit Ratio Treatment
 *
 * Measures object cache effectiveness - critical indicator of whether caching is working.
 *
 * **What This Check Does:**
 * 1. Queries object cache for hit/miss statistics
 * 2. Calculates hit ratio percentage (target: > 80%)
 * 3. Identifies which cache backend is active (Redis, Memcached, APCu)
 * 4. Checks for cache key collisions or evictions
 * 5. Measures query time with/without cache hits
 * 6. Estimates performance impact of low hit ratio
 *
 * **Why This Matters:**
 * Object cache stores frequently-accessed data (posts, options, users) in fast memory. A cache hit
 * returns data from memory in 1ms. A cache miss queries the database in 50-200ms. High hit ratio
 * (> 80%) means 80% of data lookups return instantly from memory. Low hit ratio (< 40%) means
 * 60% of lookups hit slow database queries. With 1 million page requests, low cache hit ratio
 * means 600,000+ unnecessary database queries per request cycle.\n *
 * **Real-World Scenario:**\n * WordPress e-commerce site had cache hit ratio of 15% (very bad). Most queries were hitting database
 * instead of cache. Investigation revealed cache keys not persisting across requests due to
 * incorrect cache backend configuration. Fixing configuration improved hit ratio to 87%.
 * Database load dropped 80%. Page load time decreased from 4.2s to 0.9s.\n * Site could handle 10x concurrent users. Hosting was downgradable to half the infrastructure.
 * Cost: 2 hours debugging. Value: $2,400/month in hosting cost reduction.\n *
 * **Business Impact:**\n * - High database load (CPU at 100%, queries queue up)\n * - Slow page loads proportional to cache miss rate (1% hit = 99% of queries slow)\n * - Database server becomes bottleneck (scales poorly with traffic)\n * - Scaling requires adding database servers instead of just caching\n * - Server crashes during traffic spikes (no cache buffer)\n * - Hosting costs 300-500% higher than necessary\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents database bottleneck cascade failures\n * - #9 Show Value: Delivers 50-100x performance improvement with proper cache hits\n * - #10 Talk-About-Worthy: "Site stayed fast under 10x traffic" is huge\n *
 * **Related Checks:**\n * - Object Cache Not Installed (pre-requisite for this check)\n * - Database Query Optimization (related performance metric)\n * - Plugin Performance Under Load (cache hit ratio indicates scale)\n * - Server Load Monitor (cache affects overall load)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/cache-hit-ratio\n * - Video: https://wpshadow.com/training/object-cache-101 (7 min)\n * - Advanced: https://wpshadow.com/training/cache-invalidation-patterns (13 min)\n *
 * @package    WPShadow\n * @subpackage Treatments\n * @since      1.6033.2057\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Cache Hit Ratio Treatment Class\n *\n * Analyzes object cache effectiveness through hit/miss ratio measurement.
 *
 * @since 1.6033.2057
 */
class Treatment_Cache_Hit_Ratio extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-hit-ratio';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Hit Ratio';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures object cache effectiveness via hit ratio';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes cache statistics to determine effectiveness.
	 * Good hit ratio: >80%
	 * Poor hit ratio: <50%
	 *
	 * @since  1.6033.2057
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cache_Hit_Ratio' );
	}
}
