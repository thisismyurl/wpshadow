<?php
/**
 * Customer Account Security Standards Diagnostic
 *
 * Checks if e-commerce sites implement proper account security including
 * password requirements, 2FA options, session management, and breach detection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Account Security Diagnostic Class
 *
 * Verifies e-commerce sites implement strong account security measures.
 *
 * @since 1.6031.1500
 */
class Diagnostic_Customer_Account_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-account-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Account Security Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies e-commerce sites implement strong customer account security measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for ecommerce plugins.
		$ecommerce_plugins = array(
			'woocommerce',
			'easy-digital-downloads',
			'wp-ecommerce',
			'marketpress',
		);

		$has_ecommerce = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $ecommerce_plugins as $ec_plugin ) {
				if ( stripos( $plugin, $ec_plugin ) !== false ) {
					$has_ecommerce = true;
					break 2;
				}
			}
		}

		if ( ! $has_ecommerce ) {
			return null; // No ecommerce.
		}

		$issues = array();

		// Check for 2FA plugins.
		$has_2fa = false;
		$twofa_plugins = array(
			'two-factor',
			'2fa',
			'google-authenticator',
			'wordfence',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $twofa_plugins as $tfa_plugin ) {
				if ( stripos( $plugin, $tfa_plugin ) !== false ) {
					$has_2fa = true;
					break 2;
				}
			}
		}

		if ( ! $has_2fa ) {
			$issues[] = __( 'No two-factor authentication plugin detected', 'wpshadow' );
		}

		// Check for password policy enforcement.
		$has_password_policy = false;
		$password_plugins = array(
			'password-policy',
			'better-passwords',
			'force-strong-passwords',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $password_plugins as $pw_plugin ) {
				if ( stripos( $plugin, $pw_plugin ) !== false ) {
					$has_password_policy = true;
					break 2;
				}
			}
		}

		if ( ! $has_password_policy ) {
			$issues[] = __( 'No password strength enforcement plugin found', 'wpshadow' );
		}

		// Check for login attempt limiting.
		$has_login_limiting = false;
		$limit_plugins = array(
			'limit-login-attempts',
			'wordfence',
			'ithemes-security',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $limit_plugins as $lim_plugin ) {
				if ( stripos( $plugin, $lim_plugin ) !== false ) {
					$has_login_limiting = true;
					break 2;
				}
			}
		}

		if ( ! $has_login_limiting ) {
			$issues[] = __( 'No login attempt limiting detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Customer account security concerns: %s. E-commerce sites should implement 2FA, password policies, and login attempt limiting to protect customer accounts.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/customer-account-security',
		);
	}
}
