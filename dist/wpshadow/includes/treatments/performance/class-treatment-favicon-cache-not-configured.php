<?php
/**
 * Favicon Cache Not Configured Treatment
 *
 * Checks if favicon cache is configured.
 * Favicon = website icon shown in browser tabs.
 * No cache = browser requests favicon on every page.
 * With cache = favicon cached, one request per session.
 *
 * **What This Check Does:**
 * - Checks Cache-Control headers on favicon requests
 * - Validates favicon.ico max-age setting
 * - Tests browser caching behavior
 * - Checks for far-future expires headers
 * - Validates favicon delivery method
 * - Returns severity if no cache headers set
 *
 * **Why This Matters:**
 * Browser requests favicon.ico on every page without cache headers.
 * Small file (1-2KB) but adds up. 100 page views = 100 unnecessary requests.
 * With cache headers (1 year expiry): 1 request per user. 99% reduction.
 *
 * **Business Impact:**
 * Site gets 50K page views/day. Favicon has no cache headers.
 * Browsers request favicon 50K times/day (unnecessary traffic).
 * Bandwidth: 100MB/day wasted. Server: 50K unnecessary requests.
 * Added cache header (Cache-Control: max-age=31536000, 1 year).
 * Favicon requests drop to ~5K/day (only new visitors).
 * Bandwidth saved: 90MB/day = 2.7GB/month. Server load reduced.
 * Cost savings: $15/month bandwidth. Setup time: 2 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Every resource optimized
 * - #9 Show Value: Measurable bandwidth savings
 * - #10 Beyond Pure: Attention to detail
 *
 * **Related Checks:**
 * - Browser Caching Configuration (broader caching)
 * - Static Asset Optimization (related resources)
 * - HTTP Headers Validation (cache header checks)
 *
 * **Learn More:**
 * Favicon optimization: https://wpshadow.com/kb/favicon-cache
 * Video: Caching static assets (8min): https://wpshadow.com/training/static-caching
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Favicon Cache Not Configured Treatment Class
 *
 * Detects missing favicon cache.
 *
 * **Detection Pattern:**
 * 1. Locate favicon file (favicon.ico or defined in HTML)
 * 2. Make HTTP HEAD request to favicon URL
 * 3. Check Cache-Control header
 * 4. Validate max-age value (should be 31536000+ for 1 year)
 * 5. Check Expires header as fallback
 * 6. Return if no cache headers or short duration
 *
 * **Real-World Scenario:**
 * Updated .htaccess to cache favicon:
 * <FilesMatch "\.(ico|png|jpg|gif)$">
 *   Header set Cache-Control "max-age=31536000, public"
 * </FilesMatch>
 * Result: favicon requests dropped 90%. Bandwidth usage reduced.
 * Lighthouse performance: +2 points (reduced requests).
 *
 * **Implementation Notes:**
 * - Checks favicon cache headers
 * - Validates cache duration
 * - Tests browser caching behavior
 * - Severity: low (small file, minor impact)
 * - Treatment: add Cache-Control header with long max-age
 *
 * @since 0.6093.1200
 */
class Treatment_Favicon_Cache_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-cache-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon Cache Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if favicon cache is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Favicon_Cache_Not_Configured' );
	}
}
