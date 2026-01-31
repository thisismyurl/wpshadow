<?php
/**
 * Authentication Brute Force Protection Diagnostic
 *
 * Verifies that brute force protection is in place to prevent
 * attackers from guessing admin passwords.
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
 * Diagnostic_Authentication_Brute_Force_Protection Class
 *
 * Checks for login rate limiting and brute force protection mechanisms.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Authentication_Brute_Force_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'brute-force-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Brute Force Attack Protection Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies login rate limiting and brute force protection';

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
	 * @return array|null Finding array if protection is missing, null otherwise.
	 */
	public static function check() {
		$protections = self::check_protection_mechanisms();

		if ( $protections['has_protection'] ) {
			return null; // Good - protection is in place
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'No brute force protection detected. Site is vulnerable to credential guessing attacks.', 'wpshadow' ),
			'severity'      => 'high',
			'threat_level'  => 75,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/brute-force-protection',
			'family'        => self::$family,
			'meta'          => array(
				'protection_enabled' => false,
				'protection_type'    => 'None',
				'attempts_before_lockout' => 'Unlimited',
				'risk_level'         => __( 'HIGH - Attackers can try unlimited passwords' ),
				'recommended_solution' => array(
					__( 'Install Wordfence Security' ),
					__( 'Install iThemes Security' ),
					__( 'Or: Limit login attempts via web server config' ),
				),
			),
			'details'       => array(
				'attack_scenario' => array(
					__( 'Attacker runs: wpscan --url site.com -P /path/to/wordlist.txt' ),
					__( 'WPScan tries 1000s of password combinations against /wp-login.php' ),
					__( 'Without rate limiting, attacker gets unlimited attempts' ),
					__( 'If admin password is weak (e.g., "admin123"), attacker succeeds' ),
					__( 'Result: Full site compromise' ),
				),
				'protection_methods' => array(
					'Option 1: Security Plugin (Recommended)' => array(
						__( 'Install Wordfence: Best protection, 24/7 monitoring' ),
						__( 'Install iThemes Security: Good protection, user-friendly' ),
						__( 'Install Sucuri Security: Affordable, includes malware scanning' ),
						__( 'Cost: $0-200/year, setup time: 5 minutes' ),
					),
					'Option 2: Web Server Config' => array(
						'Apache: <Limit POST>' => __( 'Restrict /wp-login.php to 5 requests/minute' ),
						'Nginx: limit_req_zone' => __( 'Limit connection rate via IP' ),
						'CloudFlare: Rate Limiting' => __( 'Block IPs attempting 50+ logins/hour' ),
					),
					'Option 3: Change Login URL' => array(
						__( 'Rename wp-login.php → wp-secret-login-xyz.php' ),
						__( 'Dramatically reduces automated attack volume' ),
						__( 'Requires plugin: WPS Hide Login' ),
					),
				),
				'best_practices'     => array(
					__( 'Never use "admin" as username' ),
					__( 'Require strong passwords: 12+ chars, mixed case, numbers, symbols' ),
					__( 'Enable 2FA (two-factor authentication) for all admins' ),
					__( 'Limit login attempts to 5 per 15 minutes' ),
					__( 'Lock account after 10 failed attempts' ),
					__( 'Monitor wp-admin logins and failed attempts' ),
					__( 'Use IP whitelisting if possible' ),
				),
			),
		);
	}

	/**
	 * Check for brute force protection mechanisms.
	 *
	 * @since  1.2601.2148
	 * @return array Protection status.
	 */
	private static function check_protection_mechanisms() {
		$protections = array(
			'has_protection' => false,
			'methods'        => array(),
		);

		// Check for security plugins
		$security_plugins = array(
			'wordfence/wordfence.php' => 'Wordfence',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security',
			'sucuri-scanner/sucuri.php' => 'Sucuri Security',
			'jetpack/jetpack.php' => 'Jetpack',
		);

		foreach ( $security_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$protections['has_protection'] = true;
				$protections['methods'][] = $plugin_name;
			}
		}

		// Check for 2FA
		if ( is_plugin_active( 'two-factor/two-factor.php' ) || 
			is_plugin_active( 'wp-2fa/wp-2fa.php' ) ) {
			$protections['methods'][] = '2FA Plugin';
		}

		// Check for changed login URL
		if ( defined( 'WPMS_HIDE_LOGIN_URL' ) || get_option( 'wps_hide_login_page' ) ) {
			$protections['methods'][] = 'Custom Login URL';
		}

		return $protections;
	}
}
