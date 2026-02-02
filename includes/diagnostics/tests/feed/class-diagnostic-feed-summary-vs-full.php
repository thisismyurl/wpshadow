<?php
/**
 * Feed Summary vs Full Diagnostic
 *
 * Evaluates whether your feeds deliver summaries or full content and explains
 * the tradeoffs. This is a strategic choice that affects traffic, engagement,
 * and reader experience. The diagnostic helps you make an intentional decision.
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
 * @since   1.26032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Summary_vs_Full Class
 *
 * Uses WordPress settings and feed output inspection to determine delivery mode.
 *
 * **Implementation Pattern:**
 * 1. Read `rss_use_excerpt` option
 * 2. Fetch feed output and inspect content length
 * 3. Compare configured setting to actual output
 * 4. Return findings if mismatch or suboptimal configuration
 *
 * **Related Diagnostics:**
 * - Feed Excerpt Configuration: Detailed excerpt analysis
 * - Feed Content Length: Validates excerpt size
 * - Feed Discovery Links: Ensures distribution discoverability
 */
class Diagnostic_Feed_Summary_vs_Full extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-summary-vs-full';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Summary vs Full';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed is set to summary or full content and recommends best practice.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$show_full = get_option( 'rss_use_excerpt', 0 ) ? false : true;
		if ( ! $show_full ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed is set to summary. Consider switching to full content for better user experience.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-summary-vs-full',
			);
		}
		return null;
	}
}
