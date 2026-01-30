<?php
/**
 * Automatic Update Email Generation Diagnostic
 *
 * Tests whether automatic update notification emails are sent.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Automatic_Update_Email_Generation
 *
 * Verifies automatic update notification emails are properly configured and sent.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Automatic_Update_Email_Generation extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		// Check if auto-updates are enabled.
		$auto_updates_enabled = get_option( 'auto_core_update_email' ) || 
			defined( 'AUTOMATIC_UPDATER_DISABLED' ) && ! AUTOMATIC_UPDATER_DISABLED;

		if ( ! $auto_updates_enabled ) {
			// Not necessarily bad, just disabled.
			return null;
		}

		// If auto-updates enabled, verify email function works.
		if ( ! function_exists( 'wp_mail' ) ) {
			return array(
				'id'           => 'automatic-update-email-generation',
				'title'        => __( 'Email Function Not Available', 'wpshadow' ),
				'description'  => __( 'wp_mail() function is not available. Auto-update emails may not send. Check your email configuration.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/automatic_update_email_generation',
				'meta'         => array(
					'wp_mail_available' => false,
				),
			);
		}

		// Check if admin email is set.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return array(
				'id'           => 'automatic-update-email-generation',
				'title'        => __( 'Invalid Admin Email', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: admin email */
					__( 'Admin email is not set or invalid: "%s". Auto-update emails cannot be sent. Set a valid admin email in Settings > General.', 'wpshadow' ),
					$admin_email
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/automatic_update_email_generation',
				'meta'         => array(
					'admin_email'  => $admin_email,
					'is_valid'     => is_email( $admin_email ),
				),
			);
		}

		return null;
	}
}
