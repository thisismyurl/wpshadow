<?php

declare(strict_types=1);
/**
 * Security Headers Implementation Diagnostic
 *
 * Philosophy: Security headers protect users and site
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Security_Headers_Implementation extends Diagnostic_Base {

	public static function check(): ?array {
		// Check for security headers by making a request to the site
		$response = wp_remote_head( home_url(), array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers         = wp_remote_retrieve_headers( $response );
		$missing_headers = array();

		// Check for important security headers
		if ( ! isset( $headers['X-Frame-Options'] ) && ! isset( $headers['x-frame-options'] ) ) {
			$missing_headers[] = 'X-Frame-Options';
		}

		if ( ! isset( $headers['X-Content-Type-Options'] ) && ! isset( $headers['x-content-type-options'] ) ) {
			$missing_headers[] = 'X-Content-Type-Options';
		}

		if ( ! isset( $headers['X-XSS-Protection'] ) && ! isset( $headers['x-xss-protection'] ) ) {
			$missing_headers[] = 'X-XSS-Protection';
		}

		if ( ! isset( $headers['Referrer-Policy'] ) && ! isset( $headers['referrer-policy'] ) ) {
			$missing_headers[] = 'Referrer-Policy';
		}

		if ( empty( $missing_headers ) ) {
			return null;
		}

		return array(
			'id'            => 'seo-security-headers-implementation',
			'title'         => 'Security Headers Missing',
			'description'   => sprintf( 'Missing security headers: %s', implode( ', ', $missing_headers ) ),
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/security-headers/',
			'training_link' => 'https://wpshadow.com/training/http-security/',
			'auto_fixable'  => true,
			'threat_level'  => 65,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests whether required security headers are present in HTTP response.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__security_headers_implementation(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ All required security headers are present',
			);
		}

		$missing = isset( $result['description'] ) ? preg_match_all( '/([A-Z-]+)/', $result['description'], $m ) : 0;
		return array(
			'passed'  => false,
			'message' => '✗ Missing security headers detected: ' . $result['description'],
		);
	}
}
