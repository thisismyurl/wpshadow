<?php
/**
 * Two-Factor Authentication Status Diagnostic
 *
 * Verifies that admin users have two-factor authentication enabled
 * to prevent unauthorized account takeovers.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Two_Factor_Authentication Class
 *
 * Checks if 2FA is enabled for admin users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Two_Factor_Authentication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies 2FA is enabled for admin accounts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if 2FA not enabled, null otherwise.
	 */
	public static function check() {
		$twofa_status = self::check_2fa_status();

		if ( $twofa_status['enabled'] ) {
			return null; // 2FA is configured
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'Two-factor authentication not enabled. Admin accounts are vulnerable to password compromise.', 'wpshadow' ),
			'severity'      => 'high',
			'threat_level'  => 80,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/enable-2fa',
			'family'        => self::$family,
			'meta'          => array(
				'twofa_enabled'         => false,
				'admin_accounts_at_risk' => __( 'All admin accounts without 2FA are vulnerable' ),
				'primary_attack'        => 'Password brute force, credential stuffing',
				'breach_risk'           => '99% of account compromises exploitable with 2FA' ),
				'solutions'             => array(
					'Google Authenticator',
					'Authy',
					'Microsoft Authenticator',
					'Hardware keys (YubiKey)',
				),
			),
			'details'       => array(
				'why_2fa_critical'  => array(
					__( 'Even strong passwords can be compromised' ),
					__( 'Reused passwords from other breaches' ),
					__( 'Phishing attacks succeed with or without passwords' ),
					__( '2FA prevents account takeover even if password leaked' ),
				),
				'setup_options'     => array(
					'Option 1: WordPress Core (Built-in)' => array(
						'App-based: Google Authenticator, Authy, Microsoft Authenticator',
						'SMS-based: Text message codes (less secure)',
						'Email-based: Email codes (least secure)',
						'Hardware key: YubiKey, Google Titan (most secure)',
					),
					'Option 2: Security Plugins (Recommended)' => array(
						'Wordfence: Complete 2FA + brute force protection',
						'iThemes Security: Easy 2FA setup',
						'Duo Security: Enterprise-grade 2FA',
					),
				),
				'quick_setup_steps'  => array(
					'Step 1' => __( 'Install WordPress Two-Factor plugin or use Wordfence' ),
					'Step 2' => __( 'Go to User Profile → Two-Factor Options' ),
					'Step 3' => __( 'Choose method: App (recommended) or SMS' ),
					'Step 4' => __( 'Scan QR code with authenticator app' ),
					'Step 5' => __( 'Save backup codes in secure location' ),
					'Step 6' => __( 'Test logout/login with 2FA enabled' ),
					'Step 7' => __( 'Require 2FA for all admin users' ),
				),
				'authenticator_apps'  => array(
					'Google Authenticator' => 'Free, most popular, reliable',
					'Authy' => 'Free, backup codes, multi-device sync',
					'Microsoft Authenticator' => 'Free, integrated with Microsoft accounts',
					'FreeOTP' => 'Free, open-source, privacy-focused',
					'YubiKey' => 'Hardware key, highest security',
				),
				'enforcement'        => array(
					__( 'Consider making 2FA mandatory for admins' ),
					__( 'Send deadline notification to non-compliant admins' ),
					__( 'Monitor 2FA status monthly' ),
					__( 'Revoke access for admins without 2FA after deadline' ),
				),
			),
		);
	}

	/**
	 * Check 2FA status.
	 *
	 * @since  1.2601.2148
	 * @return array 2FA status.
	 */
	private static function check_2fa_status() {
		// Check if 2FA plugin is active
		$twofa_plugins = array(
			'two-factor/two-factor.php',
			'wp-2fa/wp-2fa.php',
			'wordfence/wordfence.php',
		);

		foreach ( $twofa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return array(
					'enabled' => true,
					'plugin'  => $plugin,
				);
			}
		}

		return array(
			'enabled' => false,
		);
	}
}
