<?php
/**
 * Feed Caching Performance Treatment
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
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Caching_Performance Class
 *
 * Uses WordPress feed cache settings to validate performance configuration.
 *
 * **Implementation Pattern:**
 * 1. Inspect SimplePie cache settings
 * 2. Validate cache duration > 0
 * 3. Check for disabled cache constants/filters
 * 4. Return findings with tuning guidance
 *
 * **Related Treatments:**
 * - Feed URL Accessibility: Ensures feed availability
 * - Feed Content Length: Optimizes payload size
 * - Database Performance: Detects query bottlenecks
 */
class Treatment_Feed_Caching_Performance extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-caching-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Caching Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed caching is enabled and configured for performance.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Feed_Caching_Performance' );
	}
}
