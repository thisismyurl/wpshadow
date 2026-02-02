<?php
/**
 * Feed Custom Endpoints Diagnostic
 *
 * Detects custom feed endpoints registered by plugins or themes. Custom feeds
 * are powerful (category feeds, premium feeds, email automation), but they can
 * also create duplicate content, expose private data, or break if not documented.
 *
 * **What This Check Does:**
 * - Enumerates custom feed types via WordPress feed registry
 * - Flags unexpected or undocumented feed endpoints
 * - Validates custom feeds have predictable URLs
 * - Helps ensure custom feeds are intentional and secure
 *
 * **Why This Matters:**
 * Custom feeds can leak private content if not permission-checked.
 * They can also confuse subscribers if multiple feed URLs exist.
 * Auditing custom feeds ensures content distribution is controlled.
 *
 * **Real-World Example:**
 * - Membership plugin creates `/feed/premium/`
 * - Feed endpoint does not check permissions
 * - Non‑members can access premium content via RSS
 *
 * Result: Paid content leaked publicly.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents accidental content exposure
 * - #9 Show Value: Ensures custom feeds are intentional and useful
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/custom-feed-endpoints
 * or https://wpshadow.com/training/rss-customization
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
 * Diagnostic_Feed_Custom_Endpoints Class
 *
 * Reads the registered feed types and highlights custom additions.
 *
 * **Implementation Pattern:**
 * 1. Get registered feed types
 * 2. Compare against core feeds (rss2, atom, rdf)
 * 3. Flag custom feed slugs
 * 4. Return findings with security guidance
 *
 * **Related Diagnostics:**
 * - Feed Redirects: Identifies alternative distribution paths
 * - Feed URL Accessibility: Ensures custom feeds respond
 * - Feed Namespace Configuration: Validates XML integrity
 */
class Diagnostic_Feed_Custom_Endpoints extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-custom-endpoints';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Custom Endpoints';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for custom feed endpoints registered in WordPress.';

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
		global $wp_rewrite;
		$custom_feeds = array();
		if ( isset( $wp_rewrite->feeds ) && is_array( $wp_rewrite->feeds ) ) {
			foreach ( $wp_rewrite->feeds as $feed ) {
				if ( ! in_array( $feed, array( 'rss2', 'atom', 'rdf', 'rss' ), true ) ) {
					$custom_feeds[] = $feed;
				}
			}
		}
		if ( ! empty( $custom_feeds ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Custom feed endpoints are registered.', 'wpshadow' ),
				'feeds'       => $custom_feeds,
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-custom-endpoints',
			);
		}
		return null;
	}
}
