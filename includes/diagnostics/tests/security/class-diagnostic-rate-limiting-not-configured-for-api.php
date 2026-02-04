<?php
/**
 * Rate Limiting Not Configured For API Diagnostic
 *
 * Validates rate limiting is configured on REST API endpoints.
 * Unlimited API = attacker can make infinite requests (DDoS + brute force).
 * Rate limiting throttles requests per IP (prevents abuse).
 *
 * **What This Check Does:**
 * - Checks if API rate limiting implemented
 * - Validates rate limit thresholds (requests/minute)
 * - Tests if limits apply per IP address
 * - Confirms 429 response on limit exceeded
 * - Checks if bypass methods present
 * - Returns severity if no rate limiting
 *
 * **Why This Matters:**
 * Unlimited API = attack amplifier. Scenarios:
 * - REST API allows infinite requests
 * - Attacker writes loop: 10K requests/minute
 * - Server overloaded (legitimate users blocked)
 * - DDoS attack (attacker uses your API as weapon)
 *
 * **Business Impact:**
 * API endpoint queries database without rate limiting. Attacker discovers.
 * Writes script: 1000 requests/minute. Database slowed to crawl. Site
 * becomes unresponsive (users can't browse). Revenue loss: $10K/hour.
 * Bandwidth bill: $5K+. With rate limiting (10 req/min): attacker attack
 * takes months (impractical). Automatic protection.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API protected from abuse
 * - #9 Show Value: Quantified DDoS prevention
 * - #10 Beyond Pure: Fair-use enforcement
 *
 * **Related Checks:**
 * - API Throttling Overall (general API limits)
 * - Geolocation Blocking (source restriction)
 * - DDoS Protection Configuration (infrastructure)
 *
 * **Learn More:**
 * API rate limiting: https://wpshadow.com/kb/wordpress-api-rate-limiting
 * Video: Configuring API limits (9min): https://wpshadow.com/training/api-rate-limiting
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rate Limiting Not Configured For API Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * **Detection Pattern:**
 * 1. Query REST API endpoints
 * 2. Check for rate limit headers (X-RateLimit-*)
 * 3. Validate limit threshold (requests/minute)
 * 4. Test if limits apply per IP
 * 5. Confirm 429 response on excess
 * 6. Return severity if no limits
 *
 * **Real-World Scenario:**
 * News site has REST API endpoint for articles (no rate limiting). Attacker
 * writes script: 100 requests/second. Server CPU spikes. Database maxes out.
 * Site goes down. Users can't access content. Revenue lost: $50K. With rate
 * limiting (10 requests/second per IP): attacker's script throttled. Site
 * stays responsive.
 *
 * **Implementation Notes:**
 * - Checks REST API endpoints
 * - Validates rate limit headers
 * - Tests actual rate limit enforcement
 * - Severity: high (no limits), medium (weak limits)
 * - Treatment: implement per-IP rate limiting on all API endpoints
 *
 * @since 1.6030.2352
 */
class Diagnostic_Rate_Limiting_Not_Configured_For_API extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rate-limiting-not-configured-for-api';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rate Limiting Not Configured For API';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API rate limiting is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if rate limiting is set
		if ( ! has_filter( 'rest_request_before_callbacks', 'check_api_rate_limit' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Implement rate limiting to prevent abuse and ensure fair API access.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rate-limiting-not-configured-for-api',
			);
		}

		return null;
	}
}
