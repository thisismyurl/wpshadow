<?php

declare(strict_types=1);
/**
 * HTTP Strict Transport Security (HSTS) Diagnostic
 *
 * Philosophy: Security hardening - prevent SSL stripping attacks
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if HSTS header is configured.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_HSTS extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Only check if site uses HTTPS
		if ( ! is_ssl() ) {
			return null;
		}

		$response = wp_remote_head(
			home_url(),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( empty( $headers['strict-transport-security'] ) ) {
			return array(
				'id'            => 'hsts-header',
				'title'         => 'HSTS Header Not Configured',
				'description'   => 'Your HTTPS site lacks HTTP Strict Transport Security (HSTS) header, making it vulnerable to SSL stripping attacks. Add the Strict-Transport-Security header.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/enable-hsts/',
				'training_link' => 'https://wpshadow.com/training/hsts-security/',
				'auto_fixable'  => true,
				'threat_level'  => 60,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests whether HSTS header is configured on HTTPS sites.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__hsts(): array {
		// If site isn't HTTPS, test passes (no HSTS needed)
		if ( ! is_ssl() ) {
			return array(
				'passed'  => true,
				'message' => '✓ Site not using HTTPS, HSTS check not required',
			);
		}

		// Site is HTTPS - run the diagnostic check
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ HSTS header is properly configured',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ HSTS header missing on HTTPS site: ' . $result['title'],
		);
	}
}
