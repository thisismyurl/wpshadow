<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: HTTPS/SSL Status
 *
 * Validates that WordPress is using HTTPS/SSL encryption.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure site security with HTTPS
 */
class Test_System_SSL_Certificate extends Diagnostic_Base {


	/**
	 * Check HTTPS/SSL status
	 *
	 * @return array|null Issues found or null if HTTPS enabled
	 */
	public static function check(): ?array {
		// Check if HTTPS is enabled
		$is_ssl = is_ssl();

		// Also check the siteurl option
		$siteurl      = get_option( 'siteurl' );
		$is_https_url = strpos( $siteurl, 'https://' ) === 0;

		if ( ! $is_ssl || ! $is_https_url ) {
			return array(
				'id'           => 'ssl-certificate-not-enabled',
				'title'        => 'HTTPS Not Enabled',
				'description'  => 'Your site is not using HTTPS. Enable SSL/TLS encryption for better security.',
				'threat_level' => 80,
			);
		}

		return null; // HTTPS is enabled
	}

	/**
	 * Live test for SSL certificate diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_ssl_certificate(): array {
		$result = self::check();

		// Test 1: Check HTTPS status
		$is_ssl       = is_ssl();
		$siteurl      = get_option( 'siteurl' );
		$is_https_url = strpos( $siteurl, 'https://' ) === 0;

		// Test 2: Compare results
		if ( ! $is_ssl || ! $is_https_url ) {
			// Should return an issue
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'HTTPS is disabled (is_ssl=' . ( $is_ssl ? 'true' : 'false' ) . ', siteurl=' . $siteurl . '), but check() returned null.',
				);
			}
		} else {
			// HTTPS is enabled
			if ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'HTTPS is enabled, but check() returned: ' . wp_json_encode( $result ),
				);
			}
		}

		// All tests passed
		$https_status = $is_ssl ? 'enabled' : 'disabled';
		return array(
			'passed'  => true,
			'message' => "SSL/HTTPS check passed. Current status: {$https_status}.",
		);
	}
}
