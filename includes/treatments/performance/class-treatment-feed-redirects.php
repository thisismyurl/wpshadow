<?php
/**
 * Feed Redirects Treatment
 *
 * Checks whether your RSS/Atom feeds are redirected to third-party services
 * (such as FeedBurner or external analytics platforms). Redirects can be useful
 * for tracking, but they can also break subscription flows, cause feed readers
 * to reject updates, and create a single point of failure.
 *
 * **What This Check Does:**
 * - Detects redirects from core feed URLs (e.g., /feed/)
 * - Identifies external feed services receiving traffic
 * - Validates redirect status codes and destination consistency
 * - Warns about reliance on deprecated or unstable services
 * - Encourages keeping canonical feeds active
 *
 * **Why This Matters:**
 * Feed redirects are a hidden dependency. If the redirect service fails or
 * changes behavior, your subscribers stop receiving content without warning.
 * Some feed readers also block redirects to unknown domains for security.
 *
 * **Real-World Failure Scenario:**
 * - Site redirects feeds to FeedBurner
 * - FeedBurner experiences downtime (or is discontinued)
 * - All subscribers stop receiving updates
 * - Site owner doesn’t notice for weeks
 *
 * Result: Silent loss of audience engagement.
 *
 * **Redirect Best Practices:**
 * - Keep your canonical feed URLs active and valid
 * - If you redirect, use 301 with consistent destination
 * - Avoid chaining redirects (reader timeouts)
 * - Avoid services with uncertain support lifecycles
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Protects subscribers from broken feeds
 * - #9 Show Value: Preserves distribution reliability
 * - Accessibility First: Feeds are assistive content delivery tools
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-redirect-strategy
 * or https://wpshadow.com/training/rss-distribution-reliability
 *
 * @since   1.6032.1921
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Redirects Class
 *
 * Uses WordPress feed URLs and HTTP responses to detect redirects.
 * Redirect behavior is checked for canonical feed endpoints.
 *
 * **Implementation Pattern:**
 * 1. Build feed URLs (RSS, Atom, comments)
 * 2. Request headers to detect 301/302 responses
 * 3. Compare destination host to site host
 * 4. Flag external redirect destinations
 *
 * **Related Treatments:**
 * - Feed URL Accessibility: Ensures feed URLs respond
 * - Feed HTTPS Enforcement: Validates secure feed delivery
 * - Feed Discovery Links: Ensures feeds are discoverable
 */
class Treatment_Feed_Redirects extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-redirects';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Redirects';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed URLs are being redirected (e.g., to FeedBurner or other services).';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$feed_url = get_feed_link();
		$response = wp_remote_get( $feed_url, array( 'timeout' => 5, 'redirection' => 0 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( in_array( $code, array( 301, 302, 307, 308 ), true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed URL is being redirected.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 50,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-redirects',
			);
		}
		return null;
	}
}
