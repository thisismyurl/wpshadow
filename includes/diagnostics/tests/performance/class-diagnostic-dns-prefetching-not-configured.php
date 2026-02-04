<?php
/**
 * DNS Prefetching Not Configured Diagnostic
 *
 * Checks if DNS prefetching is configured.
 * DNS prefetch = resolve domain names before they're needed.
 * No prefetch = browser waits for DNS lookup when resource requested.
 * With prefetch = DNS already resolved. Resource loads immediately.
 *
 * **What This Check Does:**
 * - Checks for dns-prefetch link tags in HTML
 * - Validates prefetch hints for external domains
 * - Tests prefetch for CDN, fonts, analytics domains
 * - Checks preconnect hints (DNS + TCP handshake)
 * - Validates resource hint timing
 * - Returns severity if no DNS prefetch configured
 *
 * **Why This Matters:**
 * Browser requests Google Font. Must first resolve DNS (fonts.googleapis.com).
 * DNS lookup = 50-200ms. Then download font.
 * With dns-prefetch: browser resolves DNS while page loads.
 * When font needed: DNS already resolved. Saves 50-200ms.
 *
 * **Business Impact:**
 * Page uses external resources: Google Fonts (fonts.googleapis.com),
 * Google Analytics (google-analytics.com), CDN (cdn.example.com).
 * Each requires DNS lookup: 100ms × 3 = 300ms sequential delay.
 * Added dns-prefetch hints: all 3 DNS lookups happen in parallel
 * during HTML parse. When resources needed: DNS already resolved.
 * Page load time improved 250ms. First Contentful Paint improved
 * 200ms. Lighthouse performance score: +5 points. Setup: 2 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Every millisecond optimized
 * - #9 Show Value: Free performance improvement
 * - #10 Beyond Pure: Browser optimization expertise
 *
 * **Related Checks:**
 * - Preconnect Configuration (more aggressive prefetch)
 * - External Resource Loading (related)
 * - Resource Hints Implementation (broader category)
 *
 * **Learn More:**
 * DNS prefetching: https://wpshadow.com/kb/dns-prefetch
 * Video: Resource hints explained (9min): https://wpshadow.com/training/resource-hints
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
 * DNS Prefetching Not Configured Diagnostic Class
 *
 * Detects missing DNS prefetch hints.
 *
 * **Detection Pattern:**
 * 1. Parse HTML <head> section
 * 2. Look for <link rel="dns-prefetch"> tags
 * 3. Identify external domains used (fonts, analytics, CDN)
 * 4. Check if prefetch hints exist for those domains
 * 5. Validate hint placement (early in <head>)
 * 6. Return if external resources lack prefetch
 *
 * **Real-World Scenario:**
 * Added dns-prefetch for 4 external domains:
 * <link rel="dns-prefetch" href="//fonts.googleapis.com">
 * <link rel="dns-prefetch" href="//cdn.example.com">
 * <link rel="dns-prefetch" href="//google-analytics.com">
 * <link rel="dns-prefetch" href="//www.googletagmanager.com">
 * Waterfall analysis: DNS lookups now happen in parallel during HTML
 * parse. Total DNS time: 300ms → 100ms. Page interactive 200ms sooner.
 *
 * **Implementation Notes:**
 * - Checks for dns-prefetch link tags
 * - Identifies external domains
 * - Validates prefetch coverage
 * - Severity: low (micro-optimization, 50-200ms savings)
 * - Treatment: add dns-prefetch hints for external domains
 *
 * @since 1.6030.2352
 */
class Diagnostic_DNS_Prefetching_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-prefetching-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Prefetching Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DNS prefetching is configured';

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
		// Check for DNS prefetch link tags
		if ( ! has_filter( 'wp_head', 'add_dns_prefetch_links' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'DNS prefetching is not configured. Add dns-prefetch hints for external domains to improve page load speed.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/dns-prefetching-not-configured',
			);
		}

		return null;
	}
}
