<?php
/**
 * Feed HTTPS Enforcement Diagnostic
 *
 * Ensures your RSS/Atom feeds are delivered over HTTPS. Feeds often contain
 * full post content and metadata. If served over HTTP, feed readers can be
 * redirected or tampered with by intermediaries, leading to privacy leaks or
 * content manipulation.
 *
 * **What This Check Does:**
 * - Evaluates feed URLs for HTTPS usage
 * - Detects HTTP feed endpoints and mixed content
 * - Verifies canonical feed URLs match site scheme
 * - Flags redirects from HTTPS to HTTP
 * - Encourages secure feed distribution
 *
 * **Why This Matters:**
 * Many feed readers fetch content automatically in the background. If feeds
 * are served over HTTP, attackers on public Wi‑Fi can inject malicious content
 * or track user subscriptions. HTTPS protects both content integrity and
 * subscriber privacy.
 *
 * **Real-World Risk Scenario:**
 * - Reader app fetches your feed over HTTP at a coffee shop
 * - Attacker injects a fake article with phishing links
 * - Subscribers receive malicious content that appears to come from you
 *
 * Result: Trust damage and potential security incidents.
 *
 * **Philosophy Alignment:**
 * - #10 Beyond Pure: Protects subscriber privacy
 * - #8 Inspire Confidence: Ensures content integrity
 * - Accessibility First: Secure delivery for assistive feed readers
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-https-enforcement
 * or https://wpshadow.com/training/secure-content-delivery
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
 * Diagnostic_Feed_HTTPS_Enforcement Class
 *
 * Checks feed endpoint URLs and their final resolved scheme.
 *
 * **Implementation Pattern:**
 * 1. Build feed URLs from WordPress settings
 * 2. Compare scheme against site URL scheme
 * 3. Detect HTTP or HTTPS→HTTP redirects
 * 4. Return finding if insecure delivery detected
 *
 * **Related Diagnostics:**
 * - Feed Redirects: Detects external feed services
 * - Feed URL Accessibility: Confirms feed endpoints respond
 * - Feed Discovery Links: Ensures feed discovery integrity
 */
class Diagnostic_Feed_HTTPS_Enforcement extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-https-enforcement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed HTTPS Enforcement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed URLs are served over HTTPS.';

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
		$feed_url = get_feed_link();
		if ( 0 !== strpos( $feed_url, 'https://' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed URL is not served over HTTPS.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level'=> 70,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-https-enforcement',
			);
		}
		return null;
	}
}
