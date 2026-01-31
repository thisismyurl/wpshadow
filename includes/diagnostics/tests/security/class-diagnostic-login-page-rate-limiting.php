<?php
/**
 * Login Page Rate Limiting Diagnostic
 *
 * Checks if login page has rate limiting protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Rate Limiting Diagnostic
 *
 * Validates login page rate limiting configuration.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Login_Page_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login page has rate limiting protection against brute force attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$protections = array();

		// Check for rate limiting plugins
		$rate_limit_plugins = array(
			'wordfence/wordfence.php'                           => 'Wordfence Security',
			'jetpack/jetpack.php'                               => 'Jetpack',
			'sucuri-scanner/sucuri.php'                         => 'Sucuri Security',
			'wp-security-audit-log/wp-security-audit-log.php'   => 'WP Security Audit Log',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'login-security-solution/login-security-solution.php' => 'Login Security Solution',
			'brute-force-login-protection/bflp.php'             => 'Brute Force Login Protection',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$rate_limit_found = false;

		foreach ( $rate_limit_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$protections[] = $name;
				$rate_limit_found = true;
			}
		}

		// Check for custom rate limiting via hooks
		global $wp_filter;

		$custom_rate_limit = false;
		if ( isset( $wp_filter['login_init'] ) ) {
			$custom_rate_limit = true;
		}

		if ( isset( $wp_filter['wp_login_failed'] ) && isset( $wp_filter['wp_login'] ) ) {
			$custom_rate_limit = true;
		}

		if ( $custom_rate_limit ) {
			$protections[] = __( 'Custom rate limiting (via hooks)', 'wpshadow' );
			$rate_limit_found = true;
		}

		// Check for Cloudflare/WAF rate limiting
		if ( ! empty( $_SERVER['HTTP_CF_RAY'] ) ) {
			$protections[] = __( 'Cloudflare protection (WAF)', 'wpshadow' );
		}

		// Check for server-level rate limiting
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( strpos( $htaccess_content, 'mod_ratelimit' ) !== false || strpos( $htaccess_content, 'ModSecurity' ) !== false ) {
				$protections[] = __( 'Server-level rate limiting (ModSecurity/Apache)', 'wpshadow' );
			}
		}

		// If no protection found, this is an issue
		if ( ! $rate_limit_found ) {
			$issues[] = __( 'No login page rate limiting protection detected', 'wpshadow' );
			$issues[] = __( 'Site is vulnerable to brute force attacks on login page', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login page lacks rate limiting protection', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-rate-limiting',
				'details'      => array(
					'issues'              => $issues,
					'available_solutions' => array(
						__( 'Install a security plugin with rate limiting (Wordfence, Jetpack, Sucuri)', 'wpshadow' ),
						__( 'Use a custom rate limiting solution with wp-login.php monitoring', 'wpshadow' ),
						__( 'Enable server-level rate limiting (ModSecurity, Cloudflare)', 'wpshadow' ),
					),
				),
			);
		}

		// If protection found, check configuration
		if ( ! empty( $protections ) ) {
			// Check Wordfence specific settings
			if ( in_array( 'wordfence/wordfence.php', $active_plugins, true ) ) {
				$wf_options = get_option( 'wordfence_options' );
				if ( ! empty( $wf_options ) ) {
					$wf_options_array = json_decode( $wf_options, true );

					// Check if rate limiting is enabled
					if ( empty( $wf_options_array['loginSecurityEnabled'] ) ) {
						$issues[] = __( 'Wordfence login security is not enabled', 'wpshadow' );
					}
				}
			}

			// Check Limit Login Attempts Reloaded
			if ( in_array( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php', $active_plugins, true ) ) {
				$attempts = (int) get_option( 'llar_user_lockout_duration', 20 );
				if ( $attempts > 60 ) {
					$issues[] = __( 'Limit Login Attempts lockout duration is very high', 'wpshadow' );
				}
			}
		}

		// Report if issues found despite protection
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login rate limiting has configuration issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-rate-limiting',
				'details'      => array(
					'active_protections' => $protections,
					'issues'             => $issues,
				),
			);
		}

		return null;
	}
}
