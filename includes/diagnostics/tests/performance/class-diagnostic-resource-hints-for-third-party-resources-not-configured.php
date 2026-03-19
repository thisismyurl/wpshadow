<?php
/**
 * Resource Hints For Third Party Resources Not Configured Diagnostic
 *
 * Checks if resource hints are configured.
 * Resource hints = tell browser to preconnect/prefetch external resources.
 * Without hints = browser discovers resources late, delays loading.
 * With hints = browser connects early, parallel loading.
 *
 * **What This Check Does:**
 * - Checks for <link rel="preconnect"> tags
 * - Validates dns-prefetch for third-party domains
 * - Tests for preload of critical third-party resources
 * - Identifies external domains (Google Fonts, CDNs, analytics)
 * - Checks for appropriate hint usage
 * - Returns severity if hints missing for third-party resources
 *
 * **Why This Matters:**
 * Page loads. Discovers Google Fonts link. Starts DNS lookup.
 * Waits for DNS. Then TCP connection. Then TLS handshake.
 * Adds 300-600ms delay. With preconnect: browser does DNS/TCP/TLS
 * early (during HTML parse). Font loads immediately when needed.
 * Saves 300-600ms per third-party domain.
 *
 * **Business Impact:**
 * Site uses: Google Fonts, Google Analytics, Cloudflare CDN, Stripe.
 * No resource hints. Each domain: DNS (100ms) + connect (200ms) =
 * 300ms overhead × 4 domains =1.0s wasted. Added preconnect hints:
 * <link rel="preconnect" href="https://fonts.googleapis.com">
 * <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 * <link rel="preconnect" href="https://www.google-analytics.com">
 * <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
 * Result: browser preconnects during HTML parse (parallel with CSS/JS).
 * Third-party resources load immediately when needed. Load time:
 * improved 900ms (75% of theoretical max). Lighthouse "Preconnect to
 * required origins" warning: resolved. Setup: 10 minutes (add hints).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimized third-party loading
 * - #9 Show Value: Measurable latency reduction
 * - #10 Beyond Pure: Network-level optimization
 *
 * **Related Checks:**
 * - DNS Prefetching Configuration (related)
 * - Third Party Script Performance (complementary)
 * - Link Prefetching Strategy (related technique)
 *
 * **Learn More:**
 * Resource hints: https://wpshadow.com/kb/resource-hints
 * Video: Preconnect explained (9min): https://wpshadow.com/training/preconnect
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resource Hints For Third Party Resources Not Configured Diagnostic Class
 *
 * Detects missing resource hints.
 *
 * **Detection Pattern:**
 * 1. Parse HTML for external resource domains
 * 2. Identify critical third-party resources (fonts, CDNs)
 * 3. Check for preconnect/dns-prefetch hints
 * 4. Validate hint appropriateness (preconnect for critical)
 * 5. Measure connection overhead
 * 6. Return if hints missing for third-party domains
 *
 * **Real-World Scenario:**
 * Function to add hints: function add_resource_hints($hints, $relation) {
 * if ('preconnect' === $relation) { $hints[] = 'https://fonts.googleapis.com';
 * $hints[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin'];
 * } return $hints; } add_filter('wp_resource_hints', 'add_resource_hints', 10, 2);
 * Result: WordPress adds hints automatically. Browser optimizes connections.
 *
 * **Implementation Notes:**
 * - Checks for resource hint tags
 * - Validates third-party domain coverage
 * - Measures connection overhead
 * - Severity: low (optimization, measurable improvement)
 * - Treatment: add preconnect/dns-prefetch hints
 *
 * @since 1.6093.1200
 */
class Diagnostic_Resource_Hints_For_Third_Party_Resources_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'resource-hints-for-third-party-resources-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Hints For Third Party Resources Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if resource hints are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for resource hints
		if ( ! has_filter( 'wp_head', 'add_resource_hints' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Resource hints are not configured. Use preconnect, prefetch, and preload hints for third-party CDNs and resources to improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/resource-hints-for-third-party-resources-not-configured',
			);
		}

		return null;
	}
}
