<?php
/**
 * Two-Factor Authentication Status Diagnostic
 *
 * Checks if two-factor authentication is enabled for admin accounts.
 * Encourages adoption of 2FA for improved security.
 *
 * @since 0.6093.1200
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
 * Diagnostic_2FA_Status Class
 *
 * Evaluates two-factor authentication enablement.
 *
 * @since 0.6093.1200
 */
class Diagnostic_2FA_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-status';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication Status';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if two-factor authentication is enabled for admin users';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get all admin users
		$admins = get_users( array(
			'role'   => 'administrator',
			'fields' => 'ids',
		) );

		if ( empty( $admins ) ) {
			return null;
		}

		// Check for 2FA plugin
		$has_2fa_plugin = self::has_2fa_plugin();

		// Count admins with 2FA enabled
		$users_with_2fa = 0;
		foreach ( $admins as $user_id ) {
			if ( self::user_has_2fa_enabled( $user_id ) ) {
				$users_with_2fa++;
			}
		}

		// If all admins have 2FA, no issue
		if ( $users_with_2fa === count( $admins ) ) {
			return null;
		}

		// If 2FA plugin is not installed
		if ( ! $has_2fa_plugin ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of admin users without 2FA */
					__( '%d administrator account(s) do not have two-factor authentication enabled', 'wpshadow' ),
					count( $admins ) - $users_with_2fa
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-two-factor-authentication?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'admins_count'           => count( $admins ),
					'admins_with_2fa'        => $users_with_2fa,
					'admins_without_2fa'     => count( $admins ) - $users_with_2fa,
					'has_2fa_plugin'         => $has_2fa_plugin,
					'recommended_plugin'     => 'Wordfence',
				),
				'context'      => array(
					'why'            => __(
						'Administrator accounts are the highest-value targets. With password-only login, phishing, credential stuffing, and brute force succeed frequently. 2FA reduces account takeover by 99.9% according to Microsoft telemetry. OWASP lists weak authentication as a top risk. Without 2FA, any leaked or reused password can lead to full site compromise, malware injection, data exfiltration, and compliance incidents (GDPR, HIPAA, PCI-DSS).',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install a 2FA plugin: WP 2FA, Wordfence, or Two Factor.
2. Require 2FA for all administrators (not optional).
3. Use TOTP (authenticator apps) instead of SMS for stronger security.
4. Provide backup codes for account recovery.
5. Notify admins with a deadline for 2FA enrollment.
6. Audit 2FA adoption monthly and disable unused admin accounts.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'multi-factor-authentication',
				'enable_admin_2fa'
			);

			return $finding;
		}

		// If 2FA plugin is installed but not all admins enabled it
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of admin users without 2FA */
				__( '%d administrator account(s) have 2FA plugin available but not enabled', 'wpshadow' ),
				count( $admins ) - $users_with_2fa
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/enable-two-factor-authentication?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'admins_count'       => count( $admins ),
				'admins_with_2fa'    => $users_with_2fa,
				'admins_without_2fa' => count( $admins ) - $users_with_2fa,
				'has_2fa_plugin'     => $has_2fa_plugin,
			),
			'context'      => array(
				'why'            => __(
					'Having a 2FA plugin installed is not enough—each admin must enroll. Partial adoption leaves high-privilege accounts vulnerable and attackers will target the weakest admin account. A single admin compromise gives full site control. 2FA is most effective when enforced for all privileged roles.',
					'wpshadow'
				),
				'recommendation' => __(
					'1. Require 2FA for all administrators and editors.
2. Configure mandatory enrollment with a short grace period.
3. Send reminders to admins without 2FA.
4. Disable or downgrade accounts that do not enroll by the deadline.
5. Verify enrollment and test login flows for all admins.',
					'wpshadow'
				),
			),
		);

		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'multi-factor-authentication',
			'enforce_admin_2fa'
		);

		return $finding;
	}

	/**
	 * Check if a 2FA plugin is available
	 *
	 * @since 0.6093.1200
	 * @return bool
	 */
	private static function has_2fa_plugin(): bool {
		// List of popular 2FA plugins
		$plugins = array(
			'two-factor/two-factor.php',
			'wordfence/wordfence.php',
			'google-authenticator/google-authenticator-wordpress.php',
			'duo-wordpress/duo.php',
			'jetpack/jetpack.php', // Jetpack has 2FA
		);

		// Check if any plugin is active
		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if user has 2FA enabled
	 *
	 * @since 0.6093.1200
	 * @param  int $user_id User ID.
	 * @return bool
	 */
	private static function user_has_2fa_enabled( $user_id ): bool {
		// Check for Two Factor plugin meta
		if ( get_user_meta( $user_id, '_Two_Factor_Enabled' ) ||
		     get_user_meta( $user_id, '_Two_Factor_Provider' ) ) {
			return true;
		}

		// Check for Wordfence 2FA
		if ( get_user_meta( $user_id, 'wordfence_twoFactorSecret' ) ) {
			return true;
		}

		// Check for Jetpack 2FA
		if ( get_user_meta( $user_id, 'jetpack_2fa_enabled' ) ) {
			return true;
		}

		return false;
	}
}
