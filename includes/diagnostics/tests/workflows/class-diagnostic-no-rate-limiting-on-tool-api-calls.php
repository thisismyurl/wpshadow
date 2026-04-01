<?php
/**
 * No Rate Limiting on Tool API Calls
 *
 * Checks for rate limiting protection on tool API endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Rate_Limiting_On_Tool_API_Calls Class
 *
 * Validates rate limiting on tool API endpoints.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Rate_Limiting_On_Tool_API_Calls extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rate-limiting-tool-api';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool API Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates rate limiting on tool API endpoints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests rate limiting mechanisms on APIs.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for request rate limiting
		if ( ! self::has_request_rate_limiting() ) {
			$issues[] = __( 'No rate limiting on tool API requests', 'wpshadow' );
		}

		// 2. Check for per-user limits
		if ( ! self::has_per_user_limits() ) {
			$issues[] = __( 'No per-user rate limits', 'wpshadow' );
		}

		// 3. Check for throttling
		if ( ! self::has_throttling() ) {
			$issues[] = __( 'No request throttling for resource protection', 'wpshadow' );
		}

		// 4. Check for DDoS protection
		if ( ! self::has_ddos_protection() ) {
			$issues[] = __( 'No DDoS attack protection', 'wpshadow' );
		}

		// 5. Check for rate limit headers
		if ( ! self::returns_rate_limit_headers() ) {
			$issues[] = __( 'No rate limit headers in responses', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of rate limiting issues */
					__( '%d rate limiting issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/tool-api-rate-limiting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Implement rate limiting on all tool API endpoints', 'wpshadow' ),
					__( 'Enforce per-user request limits', 'wpshadow' ),
					__( 'Implement request throttling for resource protection', 'wpshadow' ),
					__( 'Add DDoS protection mechanisms', 'wpshadow' ),
					__( 'Return rate limit headers with responses', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for request rate limiting.
	 *
	 * @since 0.6093.1200
	 * @return bool True if rate limiting implemented.
	 */
	private static function has_request_rate_limiting() {
		// Check for rate limit filter
		if ( has_filter( 'wpshadow_check_rate_limit' ) ) {
			return true;
		}

		// Check for rate limit middleware
		if ( has_filter( 'wpshadow_enforce_rate_limits' ) ) {
			return true;
		}

		// Check for rate limit option
		$limits = get_option( 'wpshadow_rate_limits' );
		if ( ! empty( $limits ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for per-user limits.
	 *
	 * @since 0.6093.1200
	 * @return bool True if per-user limits enforced.
	 */
	private static function has_per_user_limits() {
		// Check for per-user limit tracking
		if ( has_filter( 'wpshadow_get_user_rate_limit' ) ) {
			return true;
		}

		// Check for user transients for rate limiting
		global $current_user_id;
		$current_user_id = get_current_user_id();

		if ( $current_user_id > 0 ) {
			$limit = get_user_meta( $current_user_id, 'wpshadow_api_rate_limit', true );
			if ( $limit !== '' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for throttling.
	 *
	 * @since 0.6093.1200
	 * @return bool True if throttling implemented.
	 */
	private static function has_throttling() {
		// Check for throttle action
		if ( has_action( 'wpshadow_throttle_requests' ) ) {
			return true;
		}

		// Check for delay implementation
		if ( has_filter( 'wpshadow_request_delay' ) ) {
			return true;
		}

		// Check for queue system
		if ( has_filter( 'wpshadow_queue_rate_limited_requests' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for DDoS protection.
	 *
	 * @since 0.6093.1200
	 * @return bool True if DDoS protection implemented.
	 */
	private static function has_ddos_protection() {
		// Check for burst detection
		if ( has_filter( 'wpshadow_detect_burst_requests' ) ) {
			return true;
		}

		// Check for IP-based limiting
		if ( has_filter( 'wpshadow_limit_by_ip' ) ) {
			return true;
		}

		// Check for request pattern analysis
		if ( has_filter( 'wpshadow_analyze_request_patterns' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for rate limit headers.
	 *
	 * @since 0.6093.1200
	 * @return bool True if headers returned.
	 */
	private static function returns_rate_limit_headers() {
		// Check for header filter
		if ( has_filter( 'wpshadow_set_rate_limit_headers' ) ) {
			return true;
		}

		// Check for X-Rate-Limit headers
		if ( has_filter( 'rest_pre_dispatch' ) ) {
			return true;
		}

		return false;
	}
}
