<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Missing WordPress Security Headers
 *
 * Detects when important WordPress security headers are missing.
 * Security headers protect against XSS, clickjacking, and other attacks.
 *
 * @since 1.2.0
 */
class Test_Missing_Security_Headers extends Diagnostic_Base {


	/**
	 * Check for missing security headers
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$missing_headers = self::detect_missing_headers();

		if ( empty( $missing_headers ) ) {
			return null;
		}

		$threat = count( $missing_headers ) * 12;
		$threat = min( 65, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => sprintf(
				'Missing %d important security headers',
				count( $missing_headers )
			),
			'metadata'      => array(
				'missing_count' => count( $missing_headers ),
				'headers'       => $missing_headers,
			),
			'kb_link'       => 'https://wpshadow.com/kb/security-headers/',
			'training_link' => 'https://wpshadow.com/training/wordpress-security-hardening/',
		);
	}

	/**
	 * Guardian Sub-Test: Security header status
	 *
	 * @return array Test result
	 */
	public static function test_security_headers_status(): array {
		$headers = self::check_all_headers();
		$present = array_filter( $headers, fn( $h ) => $h['present'] );
		$missing = array_filter( $headers, fn( $h ) => ! $h['present'] );

		return array(
			'test_name'   => 'Security Headers',
			'present'     => count( $present ),
			'missing'     => count( $missing ),
			'total'       => count( $headers ),
			'description' => sprintf( '%d/%d security headers present', count( $present ), count( $headers ) ),
		);
	}

	/**
	 * Guardian Sub-Test: X-Frame-Options
	 *
	 * @return array Test result
	 */
	public static function test_x_frame_options(): array {
		$header  = self::get_header( 'X-Frame-Options' );
		$present = $header !== null;

		return array(
			'test_name'   => 'X-Frame-Options (Clickjacking Protection)',
			'present'     => $present,
			'value'       => $header ?? 'Missing',
			'passed'      => $present && in_array( $header, array( 'DENY', 'SAMEORIGIN' ), true ),
			'description' => $present ? "Clickjacking protected: $header" : 'Missing (vulnerable to clickjacking)',
		);
	}

	/**
	 * Guardian Sub-Test: X-Content-Type-Options
	 *
	 * @return array Test result
	 */
	public static function test_x_content_type_options(): array {
		$header  = self::get_header( 'X-Content-Type-Options' );
		$present = $header !== null;

		return array(
			'test_name'   => 'X-Content-Type-Options (MIME Sniffing Protection)',
			'present'     => $present,
			'value'       => $header ?? 'Missing',
			'passed'      => $present && $header === 'nosniff',
			'description' => $present ? 'MIME sniffing protected' : 'Missing (vulnerable to MIME sniffing)',
		);
	}

	/**
	 * Guardian Sub-Test: Content-Security-Policy
	 *
	 * @return array Test result
	 */
	public static function test_content_security_policy(): array {
		$header  = self::get_header( 'Content-Security-Policy' );
		$present = $header !== null;

		return array(
			'test_name'   => 'Content-Security-Policy (XSS Protection)',
			'present'     => $present,
			'value'       => $header ? substr( $header, 0, 100 ) . '...' : 'Missing',
			'passed'      => $present,
			'description' => $present ? 'XSS protection enabled' : 'Missing (vulnerable to XSS)',
		);
	}

	/**
	 * Guardian Sub-Test: Referrer-Policy
	 *
	 * @return array Test result
	 */
	public static function test_referrer_policy(): array {
		$header  = self::get_header( 'Referrer-Policy' );
		$present = $header !== null;

		return array(
			'test_name'   => 'Referrer-Policy (Privacy Protection)',
			'present'     => $present,
			'value'       => $header ?? 'Missing',
			'passed'      => $present,
			'description' => $present ? "Privacy: $header" : 'Missing (referrer data may leak)',
		);
	}

	/**
	 * Detect missing security headers
	 *
	 * @return array List of missing headers
	 */
	private static function detect_missing_headers(): array {
		$required_headers = array(
			'X-Frame-Options',
			'X-Content-Type-Options',
			'Strict-Transport-Security', // HSTS
		);

		$missing = array();

		foreach ( $required_headers as $header ) {
			if ( self::get_header( $header ) === null ) {
				$missing[] = $header;
			}
		}

		return $missing;
	}

	/**
	 * Check all security headers
	 *
	 * @return array All headers with present status
	 */
	private static function check_all_headers(): array {
		$headers = array(
			'X-Frame-Options',
			'X-Content-Type-Options',
			'Content-Security-Policy',
			'Referrer-Policy',
			'Strict-Transport-Security',
			'Permissions-Policy',
		);

		$result = array();
		foreach ( $headers as $header ) {
			$result[ $header ] = array(
				'present' => self::get_header( $header ) !== null,
			);
		}

		return $result;
	}

	/**
	 * Get header value
	 *
	 * @param string $header Header name
	 * @return string|null Header value or null
	 */
	private static function get_header( string $header ): ?string {
		// Check if header is set (in current response headers)
		if ( function_exists( 'headers_list' ) ) {
			foreach ( headers_list() as $h ) {
				if ( stripos( $h, $header ) === 0 ) {
					return substr( $h, strlen( $header ) + 2 ); // Skip ": "
				}
			}
		}

		// Check via Apache/nginx (may not work in all environments)
		$key = 'HTTP_' . strtoupper( str_replace( '-', '_', $header ) );
		if ( isset( $_SERVER[ $key ] ) ) {
			return $_SERVER[ $key ];
		}

		return null;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Missing Security Headers';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if important HTTP security headers are configured';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
