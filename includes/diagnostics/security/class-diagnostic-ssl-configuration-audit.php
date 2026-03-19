<?php
/**
 * SSL Configuration Audit Diagnostic
 *
 * Audits your website's SSL/TLS configuration for vulnerabilities and best
 * practices. Checks certificate, ciphers, protocols, and other security aspects.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Ssl_Configuration_Audit Class
 *
 * Audits SSL/TLS configuration using Qualys SSL Labs API.
 *
 * SSL Labs (https://www.ssllabs.com/) provides a free tool for auditing
 * SSL/TLS configurations. The API analyzes your certificate, supported
 * protocols, ciphers, and other security aspects.
 *
 * Note: SSL Labs API processes asynchronously. Initial check may take
 * 5-30 minutes. Results are cached for 48 hours.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ssl_Configuration_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-configuration-audit';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SSL Configuration Audit';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Audits your SSL/TLS certificate and security configuration';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * SSL Labs API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://api.ssllabs.com/api/v3/analyze';

	/**
	 * Cache duration (48 hours - SSL Labs caches on their end).
	 *
	 * @var int
	 */
	const CACHE_TTL = 172800;

	/**
	 * Run the diagnostic check.
	 *
	 * Submits domain for SSL Labs analysis (or retrieves cached results).
	 * Parses the response and returns any SSL/TLS issues found.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if SSL issues found, null otherwise.
	 */
	public static function check() {
		// Get site domain.
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( empty( $domain ) ) {
			return null;
		}

		// Check cache first.
		$cache_key = 'wpshadow_ssl_audit_' . sanitize_key( $domain );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached; // Can return null (no issues) or array (issues found).
		}

		// Check if analysis is in progress.
		$analysis_key = "wpshadow_ssl_analysis_{$domain}";
		$analysis_status = get_transient( $analysis_key );

		if ( 'processing' === $analysis_status ) {
			// Analysis still in progress - check back later.
			set_transient( $cache_key, null, 600 ); // Cache "checking" for 10 minutes.
			return null;
		}

		// Submit analysis request.
		$result = self::analyze_domain( $domain );

		if ( is_wp_error( $result ) ) {
			// Cache error result for 1 hour.
			set_transient( $cache_key, null, 3600 );
			return null;
		}

		// Store result in cache.
		set_transient( $cache_key, $result, self::CACHE_TTL );

		return $result;
	}

	/**
	 * Submit domain for SSL Labs analysis.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to analyze.
	 * @return array|null|WP_Error Analysis results or null if no issues.
	 */
	private static function analyze_domain( string $domain ) {
		// Build API request.
		$args = array(
			'host'         => $domain,
			'publish'      => 'off', // Don't publish results.
			'startNew'     => 'off', // Use cached results if available.
			'fromCache'    => 'on',  // Prefer cache to avoid delays.
			'all'          => 'done', // Return completed results only.
		);

		// Make request.
		$response = wp_remote_get(
			add_query_arg( $args, self::API_URL ),
			array(
				'timeout' => 10,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// 429 = rate limited (SSL Labs allows 1 request per 30 seconds per IP).
		if ( 429 === $response_code ) {
			return new \WP_Error(
				'ssl_labs_rate_limited',
				__( 'SSL Labs rate limited. Checking again in 30 seconds.', 'wpshadow' )
			);
		}

		if ( 200 !== $response_code && 400 !== $response_code ) {
			return new \WP_Error(
				'ssl_labs_api_error',
				sprintf(
					__( 'SSL Labs API error (HTTP %d). Trying again later.', 'wpshadow' ),
					$response_code
				)
			);
		}

		// Parse response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return new \WP_Error(
				'ssl_labs_invalid_response',
				__( 'Invalid response from SSL Labs. Try again later.', 'wpshadow' )
			);
		}

		// Check if analysis is still in progress.
		if ( 'IN_PROGRESS' === ( $data['status'] ?? '' ) ) {
			// Mark as processing and wait for next check.
			set_transient( "wpshadow_ssl_analysis_{$domain}", 'processing', 600 );
			return null;
		}

		// Check for errors in response.
		if ( ! empty( $data['errors'] ) ) {
			return null; // SSL Labs returned an error - cache as no issues.
		}

		// Parse endpoint results.
		$issues = self::parse_results( $data, $domain );

		return ! empty( $issues ) ? $issues : null;
	}

	/**
	 * Parse SSL Labs results and extract issues.
	 *
	 * @since 1.6093.1200
	 * @param  array  $data SSL Labs response data.
	 * @param  string $domain Domain being checked.
	 * @return array|null Issues found or null.
	 */
	private static function parse_results( array $data, string $domain ) {
		$issues = array();

		// Check each endpoint (IP address serving the cert).
		$endpoints = $data['endpoints'] ?? array();
		foreach ( $endpoints as $endpoint ) {
			// Skip endpoints without assessment.
			if ( empty( $endpoint['grade'] ) ) {
				continue;
			}

			$grade = $endpoint['grade'];
			$ip = $endpoint['ipAddress'] ?? 'unknown';

			// Grades: A+ (best) through F (worst). C or lower = issues.
			if ( ! in_array( $grade, array( 'A+', 'A', 'B' ), true ) ) {
				$details = $endpoint['details'] ?? array();

				$issues[] = array(
					'ip_address' => $ip,
					'grade'      => $grade,
					'protocol_issues' => self::extract_protocol_issues( $details ),
					'cipher_issues' => self::extract_cipher_issues( $details ),
					'certificate_issues' => self::extract_cert_issues( $details ),
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$worst_grade = min( array_column( $issues, 'grade' ) );
		$severity = self::grade_to_severity( $worst_grade );
		$threat_level = self::grade_to_threat_level( $worst_grade );

		$description = sprintf(
			/* translators: %s is the SSL grade (A+, B, C, etc.) */
			__(
				'Your SSL configuration was rated %s by Qualys SSL Labs. This indicates potential security vulnerabilities that should be addressed.',
				'wpshadow'
			),
			$worst_grade
		);

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $issues,
			'item_count'      => count( $issues ),
			'kb_link'         => 'https://wpshadow.com/kb/ssl-configuration-fix',
		);
	}

	/**
	 * Extract protocol-related issues.
	 *
	 * @since 1.6093.1200
	 * @param  array $details Certificate details.
	 * @return array Protocol issues.
	 */
	private static function extract_protocol_issues( array $details ) : array {
		$issues = array();

		// Check for deprecated protocols.
		$protocols = $details['protocols'] ?? array();
		foreach ( $protocols as $protocol ) {
			$name = $protocol['name'] ?? '';
			$version = $protocol['version'] ?? '';

			// SSL 2.0, SSL 3.0, TLS1.0, TLS1.0 are deprecated.
			if ( in_array( $name, array( 'SSL', 'TLS' ), true ) ) {
				if ( in_array( $version, array( '2.0', '3.0', '1.0', '1.1' ), true ) ) {
					$issues[] = sprintf(
						__( 'Deprecated protocol: %s %s', 'wpshadow' ),
						$name,
						$version
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Extract cipher-related issues.
	 *
	 * @since 1.6093.1200
	 * @param  array $details Certificate details.
	 * @return array Cipher issues.
	 */
	private static function extract_cipher_issues( array $details ) : array {
		$issues = array();

		// Check for weak ciphers.
		$suites = $details['suites'] ?? array();
		foreach ( $suites as $suite ) {
			$ciphers = $suite['ciphers'] ?? array();
			foreach ( $ciphers as $cipher ) {
				$strength = $cipher['strength'] ?? 0;

				// Less than 128-bit = weak.
				if ( $strength < 128 ) {
					$name = $cipher['name'] ?? 'Unknown';
					$issues[] = sprintf(
						__( 'Weak cipher: %s (%d-bit)', 'wpshadow' ),
						$name,
						$strength
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Extract certificate-related issues.
	 *
	 * @since 1.6093.1200
	 * @param  array $details Certificate details.
	 * @return array Certificate issues.
	 */
	private static function extract_cert_issues( array $details ) : array {
		$issues = array();

		// Check certificate.
		$cert = $details['cert'] ?? array();
		if ( ! empty( $cert ) ) {
			$cert_data = $cert[0] ?? array();

			// Check expiration (if within 30 days = warning).
			$not_after = $cert_data['notAfter'] ?? 0;
			if ( $not_after > 0 ) {
				$days_until_expiry = ceil( ( $not_after - time() ) / 86400 );
				if ( $days_until_expiry < 30 ) {
					$issues[] = sprintf(
						__( 'Certificate expires in %d days', 'wpshadow' ),
						$days_until_expiry
					);
				}
			}

			// Check for self-signed.
			$self_signed = $cert_data['selfSigned'] ?? false;
			if ( $self_signed ) {
				$issues[] = __( 'Self-signed certificate (not trusted by browsers)', 'wpshadow' );
			}
		}

		return $issues;
	}

	/**
	 * Convert SSL grade to severity.
	 *
	 * @since 1.6093.1200
	 * @param  string $grade SSL grade (A+, A, B, C, D, E, F).
	 * @return string Severity level.
	 */
	private static function grade_to_severity( string $grade ) : string {
		switch ( $grade ) {
			case 'F':
			case 'E':
			case 'D':
				return 'critical';

			case 'C':
				return 'high';

			case 'B':
				return 'medium';

			default:
				return 'low';
		}
	}

	/**
	 * Convert SSL grade to threat level (0-100).
	 *
	 * @since 1.6093.1200
	 * @param  string $grade SSL grade.
	 * @return int Threat level.
	 */
	private static function grade_to_threat_level( string $grade ) : int {
		switch ( $grade ) {
			case 'F':
				return 95;

			case 'E':
				return 85;

			case 'D':
				return 75;

			case 'C':
				return 55;

			case 'B':
				return 35;

			case 'A':
				return 15;

			case 'A+':
				return 5;

			default:
				return 0;
		}
	}
}
