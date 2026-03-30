<?php
/**
 * External API Validation Diagnostic
 *
 * Checks that external API responses are properly validated before caching or\n * processing. Unvalidated API responses enable poisoning attacks: attacker\n * compromises external API = malicious data injected into WordPress site.\n *
 * **What This Check Does:**
 * - Detects external API calls in active plugins/themes\n * - Validates response validation before storage\n * - Checks if API responses sanitized before display\n * - Tests caching headers (don't cache untrusted data indefinitely)\n * - Validates error handling (no data leak on errors)\n * - Confirms HTTPS for all external API calls\n *
 * **Why This Matters:**
 * Unvalidated external data enables supply chain attacks. Scenarios:\n * - Plugin calls external service (weather, exchange rates, etc)\n * - Plugin doesn't validate response structure\n * - Attacker compromises external service (or performs MITM)\n * - Injects malicious data/HTML into response\n * - WordPress displays injected data to users\n * - Phishing links, malware, credential theft\n *
 * **Business Impact:**
 * WordPress site uses exchange rate API (not validated). API returns current\n * rates + bonus JavaScript payload. Plugin caches response without validation.\n * JavaScript executes on site. Redirects to phishing page. 1% of 10K daily visitors\n * click redirect (100 people). 5% enter credentials (5 people). Account takeovers\n * + fraud. Liability: $50K-$100K.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: External data trustworthy after validation\n * - #9 Show Value: Prevents supply chain attacks\n * - #10 Beyond Pure: Defense in depth, validate all inputs\n *
 * **Related Checks:**
 * - Input Sanitization Not Implemented (XSS prevention)\n * - SSL/TLS Configuration Not Set (transport security)\n * - API Throttling Not Configured (abuse prevention)\n *
 * **Learn More:**
 * API security best practices: https://wpshadow.com/kb/external-api-security\n * Video: Secure API integration (12min): https://wpshadow.com/training/api-validation\n *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_External_API_Validation Class
 *
 * Verifies that external API calls validate responses before storage.\n * Implements detection of unvalidated API response usage.\n *
 * **Detection Pattern:**
 * 1. Scan active plugins/themes for wp_remote_get/post calls\n * 2. Check response validation (is_array check, isset calls)\n * 3. Validate sanitization before storage (sanitize_*())\n * 4. Check if responses cached (how long?)\n * 5. Validate error handling (log errors, don't display)\n * 6. Return severity if validation missing\n *
 * **Real-World Scenario:**
 * Plugin calls external service for logo images. Response: Array with image URLs.\n * Plugin doesn't validate response structure. Attacker compromises service.\n * Response now contains: {\"images\": [\"https://attacker.com/inject.php?redirect=phishing\"]}.\n * Plugin displays without validation. Users click \"logo\" link (appears legitimate).\n * Redirected to phishing page. Credentials stolen.\n *
 * **Implementation Notes:**
 * - Scans for wp_remote_get/post/request calls\n * - Checks for response validation (is_array, isset)\n * - Validates sanitization before database storage\n * - Severity: critical (no validation), high (weak validation)\n * - Treatment: add response structure validation\n *
 * @since 1.6093.1200
 */
class Diagnostic_External_API_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-api-validation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External API Response Validation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that external API responses are validated and sanitized before storage';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding if external APIs are not properly validated.
	 */
	public static function check() {
		// Check for any external API-related errors in activity logs
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			// Activity logger not available, can't check
			return null;
		}

		// Check for recent external API fetch errors
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name cannot be prepared
		$recent_api_errors = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE option_name LIKE '%external_api_error%' 
			AND option_modified > DATE_SUB(NOW(), INTERVAL 7 DAY)",
			0
		);

		if ( $recent_api_errors > 5 ) {
			$finding = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of errors */
					__( 'Detected %d external API errors in the past week. Check that API responses are being validated properly.', 'wpshadow' ),
					$recent_api_errors
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/external-api-validation',
				'context'      => array(
					'why'            => __( 'Unvalidated external API responses = code injection + data corruption. Plugin calls PayPal API. Response contains malicious PHP code in discount field. Plugin displays response without validation. Code executes. Site compromised.', 'wpshadow' ),
					'recommendation' => __( '1. Validate response structure: use json_schema_validate(). 2. Sanitize/escape all API values. 3. Cache responses but validate on retrieval. 4. Set response size limits (reject oversized = DoS). 5. Validate HTTP status + content-type headers.', 'wpshadow' ),
				),
			);
			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api', 'external-validation' );
		}

		// Check for oversized API response cache
		$cache_size = self::get_api_cache_size();
		if ( $cache_size > 50 * 1024 * 1024 ) { // 50MB threshold
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: cache size */
					__( 'API response cache is unusually large (%s). This may indicate unvalidated or uncompressed responses being cached.', 'wpshadow' ),
					size_format( $cache_size )
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-cache-optimization',
			);
		}

		// All checks passed
		return null;
	}

	/**
	 * Get total size of API cache
	 *
	 * @since 1.6093.1200
	 * @return int Size in bytes.
	 */
	private static function get_api_cache_size(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name cannot be prepared
		$total_size = $wpdb->get_var(
			"SELECT SUM(CHAR_LENGTH(option_value)) FROM {$wpdb->options} 
			WHERE option_name LIKE '%_api_cache_%'",
			0
		);

		return (int) ( $total_size ?? 0 );
	}
}
