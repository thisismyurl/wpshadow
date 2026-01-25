<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Two-Factor Authentication Availability (Security)
 *
 * Checks if 2FA is available and enabled for administrators
 * Philosophy: Show value (#9) - 2FA prevents account compromise
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_TwoFactorAuthentication extends Diagnostic_Base {


	public static function check(): ?array {
		// Check if 2FA plugin is active
		$plugins       = get_plugins();
		$two_fa_active = false;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if (
				stripos( $plugin_file, '2fa' ) !== false ||
				stripos( $plugin_file, 'wordfence' ) !== false ||
				stripos( $plugin_file, 'authenticator' ) !== false
			) {
				if ( is_plugin_active( $plugin_file ) ) {
					$two_fa_active = true;
					break;
				}
			}
		}

		if ( ! $two_fa_active ) {
			return array(
				'id'           => 'two-factor-authentication',
				'title'        => __( 'Two-Factor Authentication not enabled', 'wpshadow' ),
				'description'  => __( 'Enable 2FA on admin accounts to prevent unauthorized access. Install a 2FA plugin like Wordfence.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
			);
		}

		return null;
	}

	public static function test_live_two_factor_authentication(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Two-Factor Authentication is enabled', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
