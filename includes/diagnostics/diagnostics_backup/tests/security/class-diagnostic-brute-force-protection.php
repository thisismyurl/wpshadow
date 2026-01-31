<?php
/**
 * Brute Force Protection Diagnostic
 *
 * Detects lack of login attempt throttling and brute force protection,
 * leaving wp-login.php vulnerable to password guessing attacks.
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
 * Diagnostic_Brute_Force_Protection Class
 *
 * Detects missing brute force protection.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Brute_Force_Protection extends Diagnostic_Base {

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
	protected static $title = 'Brute Force Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects lack of login attempt throttling';

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
	 * @return array|null Finding array if protection missing, null otherwise.
	 */
	public static function check() {
		$protection_check = self::check_brute_force_protection();

		if ( $protection_check['is_protected'] ) {
			return null; // Protection enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No brute force protection detected. Attackers can attempt unlimited logins. Botnets try 1000s of password combinations. One weak password = site compromised.', 'wpshadow' ),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/brute-force-protection',
			'family'       => self::$family,
			'meta'         => array(
				'protection_method' => $protection_check['method'],
				'login_url'         => wp_login_url(),
			),
			'details'      => array(
				'brute_force_attack_explained' => array(
					__( 'Automated bots try common passwords' ),
					__( 'Typical attack: 1000-10,000 attempts per hour' ),
					__( 'Target: wp-login.php, xmlrpc.php' ),
					__( 'Common passwords: admin/admin, password, 123456' ),
					__( 'Success rate: 1-5% if no protection' ),
				),
				'protection_methods'        => array(
					'Login Attempt Limiting' => array(
						'Block IP after 3-5 failed attempts',
						'Lockout duration: 15-60 minutes',
						'Plugin: Limit Login Attempts Reloaded (free)',
					),
					'Two-Factor Authentication' => array(
						'Requires second factor (phone, app)',
						'Even if password leaked, can\'t login',
						'Plugin: Two Factor, Google Authenticator',
					),
					'CAPTCHA' => array(
						'Blocks automated bots',
						'Google reCAPTCHA v3 (invisible)',
						'Plugin: reCAPTCHA by BestWebSoft',
					),
					'Cloudflare WAF' => array(
						'Blocks malicious IPs at edge',
						'Rate limiting built-in',
						'Free plan available',
					),
				),
				'plugin_recommendations'    => array(
					'Limit Login Attempts Reloaded (Free)' => array(
						'Install → Settings → Set 3 attempts, 60min lockout',
						'Email notifications on lockout',
						'IP whitelist for admins',
					),
					'Wordfence (Free)' => array(
						'Brute force protection included',
						'Two-factor authentication',
						'Firewall rules',
					),
					'iThemes Security (Free/Pro)' => array(
						'Login attempt limiting',
						'Two-factor auth',
						'Hide wp-login.php URL',
					),
				),
				'server_level_protection'   => array(
					'Fail2Ban (Linux)' => array(
						'Monitors /var/log/auth.log',
						'Bans IPs with failed attempts',
						'System-wide protection',
					),
					'ModSecurity (Apache)' => array(
						'Web application firewall',
						'OWASP Core Rule Set',
						'Blocks many attack patterns',
					),
					'Cloudflare Rate Limiting' => array(
						'Limit requests to /wp-login.php',
						'Example: 5 requests per minute',
						'Free tier includes basic limiting',
					),
				),
				'additional_hardening'      => array(
					__( 'Change default admin username' ),
					__( 'Use strong passwords (16+ characters)' ),
					__( 'Disable XML-RPC if not needed' ),
					__( 'Hide WordPress version' ),
					__( 'Monitor wp-login.php access logs' ),
				),
			),
		);
	}

	/**
	 * Check brute force protection.
	 *
	 * @since  1.2601.2148
	 * @return array Protection status.
	 */
	private static function check_brute_force_protection() {
		// Check for common protection plugins
		$protection_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'wordfence/wordfence.php'                                         => 'Wordfence',
			'better-wp-security/better-wp-security.php'                       => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php'             => 'All In One WP Security',
			'jetpack/jetpack.php'                                             => 'Jetpack (Protect module)',
		);

		foreach ( $protection_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return array(
					'is_protected' => true,
					'method'       => $plugin_name,
				);
			}
		}

		// Check if Cloudflare active (server header)
		$response = wp_remote_head( home_url() );
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['server'] ) && stripos( $headers['server'], 'cloudflare' ) !== false ) {
				return array(
					'is_protected' => true,
					'method'       => 'Cloudflare',
				);
			}
		}

		return array(
			'is_protected' => false,
			'method'       => 'None',
		);
	}
}
