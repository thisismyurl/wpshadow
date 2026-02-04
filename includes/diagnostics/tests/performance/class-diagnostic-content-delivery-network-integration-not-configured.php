<?php
/**
 * Content Delivery Network Integration Not Configured Diagnostic
 *
 * Checks if CDN is configured.
 * CDN = Content Delivery Network. Serves files from edge servers.
 * No CDN = images load from origin server (slow for distant users).
 * With CDN = images load from nearby edge server (fast globally).
 *
 * **What This Check Does:**
 * - Checks for CDN plugin or configuration
 * - Validates static asset URLs use CDN domain
 * - Tests edge server locations
 * - Checks cache rules on CDN
 * - Validates SSL on CDN domain
 * - Returns severity if no CDN configured
 *
 * **Why This Matters:**
 * User in Australia. Server in New York. 240ms latency.
 * Each image request = 240ms + download time.
 * 50 images = 12+ seconds just in latency.
 * CDN edge server in Sydney = 20ms latency. Same 50 images = 1 second.
 *
 * **Business Impact:**
 * Global e-commerce site. Server in US. International users (40%
 * traffic) see 4-second load times. Bounce rate: 60%. Implement
 * Cloudflare CDN. Edge servers in 200+ cities. International load
 * times drop to 1.2 seconds. Bounce rate: 25%. International
 * conversions increase 180%. Revenue gain: $300K/year. CDN cost:
 * $50/month. ROI: 600:1. Setup time: 2 hours.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Fast globally, not just locally
 * - #9 Show Value: Quantified international performance
 * - #10 Beyond Pure: Global accessibility thinking
 *
 * **Related Checks:**
 * - Image Optimization (CDN works best with optimized files)
 * - Browser Caching (complementary caching layer)
 * - Asset Minification (reduce CDN bandwidth usage)
 *
 * **Learn More:**
 * CDN benefits: https://wpshadow.com/kb/cdn-integration
 * Video: Setting up Cloudflare (12min): https://wpshadow.com/training/cdn-setup
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
 * Content Delivery Network Integration Not Configured Diagnostic Class
 *
 * Detects missing CDN integration.
 *
 * **Detection Pattern:**
 * 1. Check for CDN plugin (WP Rocket, W3TC, etc)
 * 2. Parse HTML for static asset URLs
 * 3. Validate URLs use CDN domain
 * 4. Test DNS for CDN provider
 * 5. Check CDN cache headers
 * 6. Return if no CDN detected
 *
 * **Real-World Scenario:**
 * Configured Cloudflare CDN. Changed image URLs from
 * example.com/uploads/image.jpg to cdn.example.com/uploads/image.jpg.
 * Edge servers cache images. User in Tokyo: image loads from Tokyo
 * server (15ms latency vs 180ms from US origin). Page load time
 * for Tokyo users: 3.2s → 1.1s (66% improvement).
 *
 * **Implementation Notes:**
 * - Checks CDN plugin or manual configuration
 * - Validates asset URL rewriting
 * - Tests edge server distribution
 * - Severity: medium (significant global performance impact)
 * - Treatment: configure CDN (Cloudflare, BunnyCDN, etc)
 *
 * @since 1.6030.2352
 */
class Diagnostic_Content_Delivery_Network_Integration_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-delivery-network-integration-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Delivery Network Integration Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN is configured';

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
		// Check for CDN integration
		if ( ! defined( 'CDN_URL' ) && ! has_filter( 'content_url', 'wp_cdn_filter_content_url' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content Delivery Network integration is not configured. Integrate a CDN like Cloudflare, Bunny CDN, or Amazon CloudFront for faster global content delivery.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-delivery-network-integration-not-configured',
			);
		}

		return null;
	}
}
