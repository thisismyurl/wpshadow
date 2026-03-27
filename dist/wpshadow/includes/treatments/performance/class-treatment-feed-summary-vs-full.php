<?php
/**
 * Feed Summary vs Full Treatment
 *
 * Evaluates whether your feeds deliver summaries or full content and explains
 * the tradeoffs. This is a strategic choice that affects traffic, engagement,
 * and reader experience. The treatment helps you make an intentional decision.
 *
 * **What This Check Does:**
 * - Reads the `rss_use_excerpt` setting
 * - Confirms real feed output matches the setting
 * - Explains tradeoffs between summary and full feeds
 * - Flags misconfigurations (setting vs output mismatch)
 *
 * **Why This Matters:**
 * Summary feeds drive clicks but add friction. Full feeds maximize convenience
 * but reduce onsite traffic. There is no one-size-fits-all answer—your business
 * model determines the best choice.
 *
 * **Decision Framework:**
 * - Choose Summary if: You rely on ads, analytics, or lead capture
 * - Choose Full if: You prioritize reader experience and reach
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Neutral guidance, no forced choice
 * - #9 Show Value: Makes impact of choice measurable
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-summary-vs-full
 * or https://wpshadow.com/training/content-distribution-strategy
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Summary_vs_Full Class
 *
 * Uses WordPress settings and feed output inspection to determine delivery mode.
 *
 * **Implementation Pattern:**
 * 1. Read `rss_use_excerpt` option
 * 2. Fetch feed output and inspect content length
 * 3. Compare configured setting to actual output
 * 4. Return findings if mismatch or suboptimal configuration
 *
 * **Related Treatments:**
 * - Feed Excerpt Configuration: Detailed excerpt analysis
 * - Feed Content Length: Validates excerpt size
 * - Feed Discovery Links: Ensures distribution discoverability
 */
class Treatment_Feed_Summary_vs_Full extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-summary-vs-full';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Summary vs Full';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed is set to summary or full content and recommends best practice.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Feed_Summary_vs_Full' );
	}
}
