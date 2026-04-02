<?php
/**
 * Media API Rate Limiting Treatment
 *
 * Validates rate limiting on media REST API endpoints. Media API endpoints
 * allow bulk download of all site images. Without rate limiting, attacker
 * scrapes entire media library (DoS + bandwidth theft).
 *
 * **What This Check Does:**
 * - Detects if media API endpoints have rate limiting
 * - Validates rate limit threshold (requests/minute)
 * - Tests if limits apply per IP and user
 * - Confirms 429 response on limit exceeded
 * - Checks if legitimate bulk operations allowed
 * - Validates CDN integration doesn't bypass limits
 *
 * **Why This Matters:**
 * Unlimited media API = bandwidth theft + DoS. Scenarios:
 * - Attacker discovers REST API media endpoint
 * - No rate limiting = download all images
 * - 10,000 images × 5MB = 50GB download
 * - Attacker's ISP maxes out (starts throttling)
 * - Your site bandwidth bill: $500+ for single attack
 *
 * **Business Impact:**
 * Photography portfolio site. REST API allows /wp/v2/media/ queries. No rate
 * limiting. Competitor scrapes all 1,000 high-res photos. Uses for competitor
 * site. Your bandwidth: 50GB transfer = $500 bill. Competitor gets free assets.
 * With rate limiting: 10 requests/minute. Scrape prevented. Cost saved.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API protected from abuse
 * - #9 Show Value: Quantified bandwidth protection
 * - #10 Beyond Pure: Fair-use enforcement
 *
 * **Related Checks:**
 * - API Throttling Not Configured (general API limits)
 * - Geolocation Blocking Not Configured (source restriction)
 * - REST API Authentication Bypass (API security)
 *
 * **Learn More:**
 * Media API protection: https://wpshadow.com/kb/wordpress-media-api-limiting
 * Video: API rate limiting setup (8min): https://wpshadow.com/training/api-limits
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media API Rate Limiting Treatment Class
 *
 * Validates rate limiting configuration on media REST API endpoints.
 *
 * **Detection Pattern:**
 * 1. Query media API endpoints (/wp/v2/media/)
 * 2. Check for rate limit headers (X-RateLimit-*)
 * 3. Validate limit threshold (requests/minute)
 * 4. Test if limits apply per IP
 * 5. Confirm 429 response on excess
 * 6. Return severity if limits missing
 *
 * **Real-World Scenario:**
 * WordPress site exposes media via REST API (default). No rate limiting.
 * Attacker discovers endpoint. Writes script: curl loop 1000 requests/min.
 * Downloads all images in bulk. Your ISP alerts: "Excessive bandwidth".
 * Invoice: $500 extra that month. With rate limiting: 10 req/min = attack
 * takes months to complete. Impractical. Attacker gives up.
 *
 * **Implementation Notes:**
 * - Checks REST API media endpoint
 * - Validates rate limit headers
 * - Tests limit enforcement
 * - Severity: high (no limits), medium (weak limits)
 * - Treatment: implement per-IP rate limiting
 *
 * @since 1.6093.1200
 */
class Treatment_Media_API_Rate_Limiting extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-api-rate-limiting';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media API Rate Limiting';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests rate limiting on media API endpoints';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Tests if REST API has rate limiting to prevent abuse
	 * and protect against DDoS attacks.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_API_Rate_Limiting' );
	}
}
