<?php
/**
 * Remember Me Cookie Security Diagnostic
 *
 * Validates "Remember Me" cookie security configuration.
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
 * Remember Me Cookie Security Diagnostic
 *
 * Checks "Remember Me" cookie security settings and best practices.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Remember_Me_Cookie_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'remember-me-cookie-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Remember Me Cookie Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates "Remember Me" cookie security configuration';

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
		$recommendations = array();

		// Check if remember me feature is enabled
		$enable_remember_me = apply_filters( 'wpshadow_enable_remember_me', true );

		// Check remember me cookie timeout
		$remember_me_timeout = REMEMBER_ME_COOKIE_TIMEOUT ?? TWO_WEEKS_IN_SECONDS;

		if ( $remember_me_timeout > MONTH_IN_SECONDS ) {
			$days = intdiv( $remember_me_timeout, DAY_IN_SECONDS );
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Remember Me cookie timeout is very long (%d days) - increases hijacking risk', 'wpshadow' ),
				$days
			);
			$recommendations[] = __( 'Reduce Remember Me timeout to 2 weeks or less', 'wpshadow' );
		}

		// Check for secure cookie settings
		if ( ! is_ssl() ) {
			$issues[] = __( 'Remember Me cookies are sent over HTTP (not HTTPS)', 'wpshadow' );
			$recommendations[] = __( 'Enable SSL/TLS for secure cookie transmission', 'wpshadow' );
		}

		// Check if persistent login tokens are securely hashed
		global $wpdb;

		// Check user meta for persistent login tokens
		$user_metas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->usermeta} WHERE meta_key LIKE %s LIMIT 10",
				'_persistent_login%'
			)
		);

		if ( ! empty( $user_metas ) ) {
			foreach ( $user_metas as $meta ) {
				// Check if token is properly hashed
				if ( ! empty( $meta->meta_value ) && strlen( $meta->meta_value ) < 40 ) {
					$issues[] = __( 'Persistent login tokens may not be properly hashed', 'wpshadow' );
					$recommendations[] = __( 'Ensure tokens are generated using wp_generate_password() and hashed with wp_hash_password()', 'wpshadow' );
					break;
				}
			}
		}

		// Check for remember me plugins
		$remember_me_plugins = array(
			'simple-persistent-login/simple-persistent-login.php',
			'persistent-login/persistent-login.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_remember_me_plugin = false;

		foreach ( $remember_me_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_remember_me_plugin = true;
				break;
			}
		}

		// Check for active sessions and remember me logins
		$sessions_table = $wpdb->prefix . 'sessions';
		$has_sessions_table = false;

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $sessions_table ) ) === $sessions_table ) {
			$has_sessions_table = true;
			$session_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$sessions_table} WHERE session_expiry > %d",
					time()
				)
			);

			if ( $session_count > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of active sessions */
					__( '%d active Remember Me sessions found - memory intensive', 'wpshadow' ),
					$session_count
				);
			}
		}

		// Check for secure cookie flags
		if ( defined( 'SECURE_AUTH_COOKIE' ) && ! SECURE_AUTH_COOKIE ) {
			$issues[] = __( 'SECURE_AUTH_COOKIE is disabled', 'wpshadow' );
		}

		// Check for device fingerprinting
		if ( ! function_exists( 'wp_get_user_agent' ) ) {
			$recommendations[] = __( 'Consider implementing device fingerprinting for Remember Me sessions', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Remember Me cookie has security issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/remember-me-cookie-security',
				'details'      => array(
					'issues'          => $issues,
					'recommendations' => $recommendations,
					'timeout_seconds' => $remember_me_timeout,
					'timeout_days'    => (int) ( $remember_me_timeout / DAY_IN_SECONDS ),
				),
			);
		}

		return null;
	}
}
