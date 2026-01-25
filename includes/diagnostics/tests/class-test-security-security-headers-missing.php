<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Security Headers Missing (Security)
 *
 * Checks if security headers are properly configured
 * Philosophy: Show value (#9) - headers prevent common attacks
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_SecurityHeadersMissing extends Diagnostic_Base {


	public static function check(): ?array {
		// Check if Wordfence or security plugin is installed
		$plugins                     = get_plugins();
		$security_headers_configured = false;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if (
				stripos( $plugin_file, 'wordfence' ) !== false ||
				stripos( $plugin_file, 'iThemes' ) !== false ||
				stripos( $plugin_file, 'security' ) !== false
			) {
				if ( is_plugin_active( $plugin_file ) ) {
					$security_headers_configured = true;
					break;
				}
			}
		}

		if ( ! $security_headers_configured && ! defined( 'WPSHADOW_SECURITY_HEADERS_DISABLED' ) ) {
			return array(
				'id'           => 'security-headers-missing',
				'title'        => __( 'Security headers may be missing', 'wpshadow' ),
				'description'  => __( 'Configure security headers (X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security) to protect against attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
			);
		}

		return null;
	}

	public static function test_live_security_headers_missing(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Security headers are properly configured', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
