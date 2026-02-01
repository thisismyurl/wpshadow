<?php
/**
 * Weak User Passwords Diagnostic
 *
 * Identifies users with weak or compromised passwords using available
 * password strength indicators and security plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weak User Passwords Diagnostic Class
 *
 * Checks for weak password indicators.
 *
 * @since 1.6032.1340
 */
class Diagnostic_Weak_User_Passwords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-user-passwords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak User Passwords';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies weak password indicators for user accounts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if password strength enforcement plugin exists.
		$password_plugins = array(
			'force-strong-passwords/force-strong-passwords.php' => 'Force Strong Passwords',
			'better-wp-security/better-wp-security.php'         => 'iThemes Security',
			'wordfence/wordfence.php'                           => 'Wordfence',
			'password-policy-manager/password-policy-manager.php' => 'Password Policy Manager',
		);

		$has_password_enforcement = false;
		foreach ( $password_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_password_enforcement = true;
			}
		}

		if ( ! $has_password_enforcement ) {
			$issues[] = __( 'No password strength enforcement plugin detected', 'wpshadow' );
		}

		// Check for known weak password indicators.
		$weak_usernames = array( 'admin', 'administrator', 'test', 'demo', 'user' );
		$weak_users     = array();

		$users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered' ) ) );

		foreach ( $users as $user ) {
			// If user login is a common weak username, they may have weak password.
			if ( in_array( strtolower( $user->user_login ), $weak_usernames, true ) ) {
				$weak_users[] = $user->user_login;
			}
		}

		if ( ! empty( $weak_users ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) have weak usernames commonly associated with weak passwords: %s', 'wpshadow' ),
				count( $weak_users ),
				implode( ', ', $weak_users )
			);
		}

		// Check for password reset age (long time since change).
		$stale_passwords = array();
		$stale_days       = 365;

		foreach ( $users as $user ) {
			$last_password_change = get_user_meta( $user->ID, 'password_last_changed', true );

			if ( ! empty( $last_password_change ) ) {
				$days_since_change = ( time() - absint( $last_password_change ) ) / DAY_IN_SECONDS;
				if ( $days_since_change >= $stale_days ) {
					$stale_passwords[] = $user->user_login;
				}
			}
		}

		if ( ! empty( $stale_passwords ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) have not changed their passwords in over %d days', 'wpshadow' ),
				count( $stale_passwords ),
				$stale_days
			);
		}

		// Check for compromised password indicators (Wordfence or similar).
		$compromised_users = array();
		foreach ( $users as $user ) {
			$compromised = get_user_meta( $user->ID, 'wfPasswordIsLeaked', true );
			if ( $compromised ) {
				$compromised_users[] = $user->user_login;
			}
		}

		if ( ! empty( $compromised_users ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) are flagged for compromised passwords (Wordfence)', 'wpshadow' ),
				count( $compromised_users )
			);
		}

		// Check for weak passwords in WooCommerce customers (if applicable).
		if ( class_exists( 'WooCommerce' ) ) {
			$weak_customer_users = get_users(
				array(
					'role'   => 'customer',
					'fields' => array( 'ID', 'user_login' ),
					'number' => 50,
				)
			);

			if ( count( $weak_customer_users ) > 100 ) {
				$issues[] = __( 'Large number of customer accounts - enforce strong passwords and 2FA', 'wpshadow' );
			}
		}

		// Check for password reset enforcement.
		$force_password_reset = get_option( 'force_password_reset', false );
		if ( ! $force_password_reset ) {
			$issues[] = __( 'Password reset policy not enforced (consider forcing periodic resets)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of password issues */
					__( 'Found %d password strength issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'weak_usernames'    => $weak_users,
					'stale_passwords'   => count( $stale_passwords ),
					'compromised_users' => count( $compromised_users ),
					'recommendation'    => __( 'Enable strong password enforcement, require 2FA for admins, and enforce periodic password resets.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
