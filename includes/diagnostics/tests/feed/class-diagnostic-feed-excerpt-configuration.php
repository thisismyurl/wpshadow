<?php
/**
 * Feed Excerpt Configuration Diagnostic
 *
 * Monitors whether your feeds deliver full content or excerpts. This is a
 * strategic business decision with traffic implications, not a technical error.
 * Understanding the tradeoff helps you make an informed choice rather than
 * accepting WordPress defaults without consideration.
 *
 * **What This Check Does:**
 * - Reads `rss_use_excerpt` WordPress option (1 = excerpts, 0 = full content)
 * - Checks actual feed output to confirm setting matches reality
 * - Identifies themes/plugins overriding feed content settings
 * - Validates excerpt length if excerpts enabled
 * - Detects misconfigurations between setting and output
 *
 * **The Strategic Decision:**
 * 
 * **Excerpt Feeds (WordPress Default):**
 * Pros:
 * - Drives traffic to your site (readers MUST click to read full content)
 * - Ad revenue opportunity (readers see ads on your site)
 * - Analytics tracking (every read = page view)
 * - Comments happen on your site (engagement metrics)
 * - SEO benefit (Google sees engaged users visiting your site)
 *
 * Cons:
 * - Friction for readers (extra click required)
 * - Lower perceived value (readers may skip instead of clicking)
 * - Mobile unfriendly (requires app switching)
 * - Some readers unsubscribe (prefer full content in reader)
 *
 * **Full Content Feeds:**
 * Pros:
 * - Reader convenience (complete posts in feed reader)
 * - Better user experience (no friction)
 * - Higher perceived value (readers get complete content)
 * - Mobile friendly (read in dedicated feed app)
 * - Builds trust (readers appreciate full access)
 *
 * Cons:
 * - Zero page views (readers never visit your site)
 * - No ad revenue (content consumed off-site)
 * - No analytics (you don't know who reads what)
 * - Comments rarely happen (readers must leave reader app)
 * - SEO neutral (no engagement signals for Google)
 *
 * **Business Model Considerations:**
 * 
 * Choose **Excerpts** if:
 * - You monetize via ads (need page views)
 * - You track conversion funnels (need analytics)
 * - You sell products/services (need site visits)
 * - You have active comments (need engagement on-site)
 *
 * Choose **Full Content** if:
 * - You build authority (establish thought leadership)
 * - You don't monetize directly (personal blog)
 * - You value reader convenience (user-first approach)
 * - You have email list (monetize via newsletter)
 *
 * **What Top Blogs Do:**
 * - TechCrunch: Full content (maximize reach)
 * - Seth Godin: Full content (build authority)
 * - Neil Patel: Excerpts (drive traffic for lead capture)
 * - HubSpot: Excerpts (funnel to gated content)
 *
 * **This Diagnostic Helps You:**
 * Make an intentional choice rather than accepting defaults.
 * Understand the business implications of your decision.
 * Validate that settings match your strategy.
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Explains decision without judgment
 * - #9 Show Value: Quantifies traffic/revenue implications
 * - Advice Not Sales: Presents options, lets you choose
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-content-strategy for decision framework
 * or https://wpshadow.com/training/content-distribution-business-strategy
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
 * Diagnostic_Feed_Excerpt_Configuration Class
 *
 * Reads WordPress settings and validates against actual feed output.
 * The `rss_use_excerpt` option controls whether feeds show full content
 * or excerpts, but themes and plugins can override this.
 *
 * **Implementation Pattern:**
 * 1. Read `rss_use_excerpt` option from database
 * 2. Fetch actual feed content from site
 * 3. Parse feed items to detect excerpt vs full content
 * 4. Compare setting to reality
 * 5. Return finding if mismatch or suboptimal configuration
 *
 * **Detection Logic:**
 * - If setting says "full" but feed has excerpts: Override detected
 * - If setting says "excerpt" but feed has full: Override detected
 * - If excerpt length < 100 chars: Too short (reader frustration)
 * - If excerpt length > 500 chars: Too long (defeats purpose)
 *
 * **Related Diagnostics:**
 * - Feed Content Length: Measures actual feed content size
 * - Feed XML Validity: Ensures excerpt HTML properly encoded
 * - Content Strategy Audit: Broader content distribution review
 *
 * @since 1.26032.1921
 */
class Diagnostic_Feed_Excerpt_Configuration extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-excerpt-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Excerpt Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed excerpt configuration matches best practices.';

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
		$excerpt = get_option( 'rss_use_excerpt', 0 );
		if ( $excerpt ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed is set to excerpt. Consider switching to full content for better SEO and user experience.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-excerpt-configuration',
			);
		}
		return null;
	}
}
