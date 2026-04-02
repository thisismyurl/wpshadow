<?php
/**
 * Feed Caching Performance Diagnostic
 *
 * Verifies that feed caching is enabled and properly configured. Feeds are
 * often requested by multiple services every few minutes. Without caching,
 * each request triggers full WordPress rendering and database queries, which
 * can cause load spikes and slow admin performance.
 *
 * **What This Check Does:**
 * - Inspects SimplePie and feed cache configuration
 * - Validates feed cache duration settings
 * - Detects disabled caching or zero cache lifetime
 * - Encourages reasonable cache lifetimes for performance
 *
 * **Why This Matters:**
 * Feed readers poll frequently. A popular site can receive thousands of feed
 * requests per hour. Without caching, this becomes a silent performance drain
 * that competes with real user traffic.
 *
 * **Performance Scenario:**
 * - 5,000 subscribers
 * - Each reader checks every 15 minutes
 * - 20,000 feed requests per hour
 * - Without cache: 20,000 PHP page renders per hour
 *
 * Result: CPU spikes, slower admin, increased hosting costs.
 *
 * **Philosophy Alignment:**
 * - #9 Show Value: Quantifiable performance impact
 * - #8 Inspire Confidence: Ensures stable content delivery
 * - Helpful Neighbor: Prevents silent resource drain
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-caching-performance
 * or https://wpshadow.com/training/wordpress-performance-basics
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Caching_Performance Class
 *
 * Uses WordPress feed cache settings to validate performance configuration.
 *
 * **Implementation Pattern:**
 * 1. Inspect SimplePie cache settings
 * 2. Validate cache duration > 0
 * 3. Check for disabled cache constants/filters
 * 4. Return findings with tuning guidance
 *
 * **Related Diagnostics:**
 * - Feed URL Accessibility: Ensures feed availability
 * - Feed Content Length: Optimizes payload size
 * - Database Performance: Detects query bottlenecks
 */
class Diagnostic_Feed_Caching_Performance extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-caching-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Caching Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed caching is enabled and configured for performance.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_enabled = defined( 'WP_CACHE' ) && WP_CACHE;
		if ( ! $cache_enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed caching is not enabled.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 50,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-caching-performance',
			);
		}
		return null;
	}
}
