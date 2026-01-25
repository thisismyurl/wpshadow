<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Admin Email Configuration (Monitoring)
 *
 * Checks if admin email is valid and configured
 * Philosophy: Show value (#9) - notifications prevent missed issues
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_AdminEmailConfiguration extends Diagnostic_Base {


	public static function check(): ?array {
		$admin_email = get_option( 'admin_email' );

		// Check if email is valid
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return array(
				'id'           => 'admin-email-configuration',
				'title'        => __( 'Admin email is invalid or missing', 'wpshadow' ),
				'description'  => __( 'Configure a valid admin email (Settings > General) to receive important site notifications.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
			);
		}

		// Check if it's the default WordPress email
		if ( strpos( $admin_email, 'WordPress' ) !== false || strpos( $admin_email, 'example.com' ) !== false ) {
			return array(
				'id'           => 'admin-email-configuration',
				'title'        => __( 'Admin email appears to be a test email', 'wpshadow' ),
				'description'  => __( 'Change the admin email to a real address to receive notifications.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
			);
		}

		return null;
	}

	public static function test_live_admin_email_configuration(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Admin email is properly configured', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
