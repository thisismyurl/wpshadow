<?php
/**
 * HTTP Caching Headers Not Configured Treatment
 *
 * Checks if HTTP caching is configured.
 * HTTP caching headers = tell browser how long to cache files.
 * No headers = browser re-downloads files on every visit.
 * With headers = browser uses cached version. 95% faster repeat visits.
 *
 * **What This Check Does:**
 * - Checks Cache-Control headers on static assets
 * - Validates Expires headers as fallback
 * - Tests max-age values (1 year for static, less for HTML)
 * - Checks ETag and Last-Modified headers
 * - Validates caching for different resource types
 * - Returns severity if caching headers missing
 *
 * **Why This Matters:**
 * Repeat visitor without cache headers = re-downloads everything.
 * 2MB page assets downloaded again. Wastes time + bandwidth.
 * With cache headers = browser uses local copy. Page loads instantly.
 * Repeat visitor experience dramatically better.
 *
 * **Business Impact:**
 * News site: 60% repeat visitors. No cache headers. Every visit
 * downloads1.0MB assets (images, CSS, JS). Bandwidth: 2.7TB/month.
 * Repeat visit load time: 3.5 seconds. Added cache headers:
 * Cache-Control: max-age=31536000 for static assets. Repeat visitors
 * load cached assets (zero downloads). Load time: 0.4 seconds (90%
 * faster). Bandwidth reduced to1.0TB/month (60% savings = $400/month).
 * User engagement increased 40% (faster = more page views). Setup: 10 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimal repeat visit experience
 * - #9 Show Value: Massive bandwidth + speed improvement
 * - #10 Beyond Pure: HTTP protocol expertise
 *
 * **Related Checks:**
 * - Browser Caching Configuration (broader check)
 * - Cache Busting Implementation (versioning)
 * - CDN Caching (edge caching)
 *
 * **Learn More:**
 * HTTP caching: https://wpshadow.com/kb/http-caching
 * Video: Browser caching explained (11min): https://wpshadow.com/training/browser-cache
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Caching Headers Not Configured Treatment Class
 *
 * Detects missing HTTP caching headers.
 *
 * **Detection Pattern:**
 * 1. Request sample static assets (images, CSS, JS)
 * 2. Check Cache-Control header presence
 * 3. Validate max-age values (31536000 for static)
 * 4. Check Expires header as fallback
 * 5. Test ETag/Last-Modified headers
 * 6. Return if headers missing or too short
 *
 * **Real-World Scenario:**
 * Configured .htaccess caching:
 * <IfModule mod_expires.c>
 *   ExpiresActive On
 *   ExpiresByType image/jpg "access plus 1 year"
 *   ExpiresByType text/css "access plus 1 year"
 *   ExpiresByType application/javascript "access plus 1 year"
 * </IfModule>
 * Result: Lighthouse "Serve static assets with efficient cache policy"
 * changed from warning to pass. Repeat visitor load time improved 85%.
 *
 * **Implementation Notes:**
 * - Checks Cache-Control and Expires headers
 * - Validates max-age duration
 * - Tests different resource types
 * - Severity: high (affects all repeat visitors)
 * - Treatment: configure server cache headers
 *
 * @since 1.6093.1200
 */
class Treatment_HTTP_Caching_Headers_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-caching-headers-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Caching Headers Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP caching is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_HTTP_Caching_Headers_Not_Configured' );
	}
}
