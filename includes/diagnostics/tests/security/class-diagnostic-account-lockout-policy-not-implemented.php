<?php
/**
 * Account Lockout Policy Not Implemented Diagnostic
 *
 * Checks account lockout.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Account_Lockout_Policy_Not_Implemented Class
 *
 * Performs diagnostic check for Account Lockout Policy Not Implemented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Account_Lockout_Policy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'account-lockout-policy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Account Lockout Policy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks account lockout';

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
		// Check for brute force protection plugins.
		$lockout_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'wordfence/wordfence.php'                                          => 'Wordfence Security',
			'all-in-one-wp-security-and-firewall/wp-security.php'             => 'All In One WP Security',
			'better-wp-security/better-wp-security.php'                       => 'iThemes Security',
			'jetpack/jetpack.php'                                             => 'Jetpack (Protect module)',
			'loginizer/loginizer.php'                                         => 'Loginizer',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $lockout_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Check for custom lockout implementation.
		$has_custom_lockout = has_filter( 'authenticate' ) || has_filter( 'wp_login_failed' );

		// Check WordPress application passwords (WP 5.6+).
		global $wp_version;
		$has_app_passwords = version_compare( $wp_version, '5.6.0', '>=' );

		// If no brute force protection detected.
		if ( ! $plugin_detected && ! $has_custom_lockout ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Account lockout policy not implemented. Brute force attacks can try thousands of password combinations. Without lockout, attackers can keep trying indefinitely. Install Limit Login Attempts Reloaded or Wordfence to automatically lock accounts after failed login attempts.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/brute-force-protection',
				'details'     => array(
					'plugin_detected'    => false,
					'has_app_passwords'  => $has_app_passwords,
					'recommendation'     => __( 'Install Limit Login Attempts Reloaded (free, 4M+ installs) for basic protection, or Wordfence Security (free, comprehensive) for advanced features.', 'wpshadow' ),
					'attack_scenario'    => array(
						'without_protection' => 'Attacker tries 10,000 passwords in 1 hour',
						'with_lockout'       => 'Account locks after 3-5 failures, attacker blocked',
						'impact'             => 'Brute force attacks become impractical',
					),
					'recommended_settings' => array(
						'max_attempts'   => '3-5 failed logins',
						'lockout_duration' => '20 minutes',
						'long_lockout'   => '24 hours after 4 lockouts',
					),
				),
			);
		}

		// No issues - brute force protection active.
		return null;
	}
}
