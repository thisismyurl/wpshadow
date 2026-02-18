<?php
/**
 * API Rate Limiting Not Configured Diagnostic
 *
 * Checks if API rate limiting is configured.
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
 * API Rate Limiting Not Configured Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * @since 1.6030.2352
 */
class Diagnostic_API_Rate_Limiting_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting Not Configured';

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
		// Check for rate limiting
		if ( ! has_filter( 'rest_request_before_callbacks', 'wp_rest_rate_limit' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Implement rate limiting on REST API endpoints to prevent abuse and DDoS attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-rate-limiting-not-configured',
				'context'       => array(
					'why'            => __( 'No rate limit = DDoS vulnerable. Real scenario: 1 attacker = 10,000 requests/second to /wp/v2/users endpoint. Server overwhelmed. Legitimate traffic blocked. Site down 45 minutes. Customers angry. Revenue lost. With rate limit: Attacker limited to 10 requests/min. Attack ineffective. Legitimate users get responses. DDoS attacks cost $20K/hour downtime.', 'wpshadow' ),
					'recommendation' => __( '1. Implement per-user rate limiting: 100 requests/minute max. 2. Implement per-IP rate limiting: 500 requests/minute. 3. Lower limits on POST endpoints: 10 requests/minute. 4. Use Redis for distributed rate limiting. 5. Track rate limits by authorization token. 6. Return HTTP 429 when limit exceeded. 7. Include Retry-After header with wait time. 8. Log rate limit violations for monitoring. 9. Whitelist trusted IPs (internal systems). 10. Test DDoS protection with load testing tools.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-rate-limiting', 'request-throttling' );
			return $finding;
		}

		return null;
	}
}
