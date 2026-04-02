<?php
/**
 * Password Security Policy Diagnostic
 *
 * Tests if strong password policies and 2FA are enforced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Security Diagnostic Class
 *
 * Evaluates password security policies including strength requirements,
 * two-factor authentication, password expiration, and related security measures.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Password_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'enforces_password_security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Password Security Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if strong password policies and 2FA are enforced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for two-factor authentication plugins.
		$total_points += 25;
		$tfa_plugins  = array(
			'two-factor/two-factor.php'                     => 'Two Factor',
			'two-factor-authentication/two-factor-login.php' => 'Two Factor Authentication',
			'google-authenticator/google-authenticator.php' => 'Google Authenticator',
			'wordfence/wordfence.php'                       => 'Wordfence (includes 2FA)',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security Pro',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'jetpack/jetpack.php'                           => 'Jetpack (includes 2FA)',
			'wp-2fa/wp-2fa.php'                             => 'WP 2FA',
		);

		$active_tfa_plugins = array();
		foreach ( $tfa_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_tfa_plugins[] = $name;
			}
		}

		if ( ! empty( $active_tfa_plugins ) ) {
			$earned_points += 25;
		}

		$stats['tfa_plugins'] = array(
			'found' => count( $active_tfa_plugins ),
			'list'  => $active_tfa_plugins,
		);

		if ( empty( $active_tfa_plugins ) ) {
			$issues[] = __( 'No two-factor authentication plugin detected', 'wpshadow' );
		}

		// Check for password strength enforcement plugins.
		$total_points += 20;
		$password_plugins = array(
			'force-strong-passwords/force-strong-passwords.php' => 'Force Strong Passwords',
			'better-passwords/better-passwords.php' => 'Better Passwords',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts',
			'password-policy-manager/password-policy-manager.php' => 'Password Policy Manager',
		);

		$active_password_plugins = array();
		foreach ( $password_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_password_plugins[] = $name;
			}
		}

		if ( ! empty( $active_password_plugins ) ) {
			$earned_points += 20;
		}

		$stats['password_plugins'] = array(
			'found' => count( $active_password_plugins ),
			'list'  => $active_password_plugins,
		);

		if ( empty( $active_password_plugins ) ) {
			$warnings[] = __( 'No password strength enforcement plugin detected', 'wpshadow' );
		}

		// Check for security suite plugins with password features.
		$total_points += 15;
		$security_suites = array(
			'wordfence/wordfence.php'         => 'Wordfence',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security Pro',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'       => 'Sucuri Security',
		);

		$active_security_suites = array();
		foreach ( $security_suites as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_security_suites[] = $name;
			}
		}

		if ( ! empty( $active_security_suites ) ) {
			$earned_points += 15;
		}

		$stats['security_suites'] = array(
			'found' => count( $active_security_suites ),
			'list'  => $active_security_suites,
		);

		// Check for login attempt limiting.
		$total_points += 15;
		$login_limit_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts',
			'loginizer/loginizer.php' => 'Loginizer',
			'wp-limit-login-attempts/wp-limit-login-attempts.php' => 'WP Limit Login Attempts',
		);

		$active_login_limit = array();
		foreach ( $login_limit_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_login_limit[] = $name;
			}
		}

		// Security suites also provide login limiting.
		if ( ! empty( $active_login_limit ) || ! empty( $active_security_suites ) ) {
			$earned_points += 15;
		}

		$stats['login_limiting'] = array(
			'found' => count( $active_login_limit ),
			'list'  => $active_login_limit,
		);

		if ( empty( $active_login_limit ) && empty( $active_security_suites ) ) {
			$warnings[] = __( 'No login attempt limiting detected', 'wpshadow' );
		}

		// Check user count and roles (high user counts increase security importance).
		$total_points += 10;
		$user_count = count_users();
		$total_users = $user_count['total_users'];
		$stats['total_users'] = $total_users;

		// Check for privileged users.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$stats['admin_users'] = count( $admin_users );

		if ( $total_users > 10 ) {
			$earned_points += 10;
			$stats['multi_user_site'] = true;
		} else {
			$stats['multi_user_site'] = false;
			$earned_points += 5; // Partial credit for smaller sites.
		}

		// Check for password age/expiration.
		$total_points += 10;
		$password_expiry_plugins = array(
			'expire-passwords/expire-passwords.php' => 'Expire Passwords',
			'password-policy-manager/password-policy-manager.php' => 'Password Policy Manager',
		);

		$active_expiry_plugins = array();
		foreach ( $password_expiry_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_expiry_plugins[] = $name;
			}
		}

		if ( ! empty( $active_expiry_plugins ) ) {
			$earned_points += 10;
		}

		$stats['password_expiry'] = array(
			'found' => count( $active_expiry_plugins ),
			'list'  => $active_expiry_plugins,
		);

		// Check for application passwords (should be disabled for security).
		$total_points += 5;
		if ( ! defined( 'WP_APPLICATION_PASSWORDS' ) || WP_APPLICATION_PASSWORDS === false ) {
			$earned_points += 5;
			$stats['app_passwords_disabled'] = true;
		} else {
			$stats['app_passwords_disabled'] = false;
			$warnings[] = __( 'Application passwords are enabled (consider disabling if not needed)', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'high';
		$threat_level = 60;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 70;
		} elseif ( $score >= 30 && $score < 60 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		// Return finding if password security is insufficient.
		if ( $score < 60 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: security score percentage */
					__( 'Password security score: %d%%. Strong password policies and two-factor authentication are critical for protecting user accounts.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/password-security',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
