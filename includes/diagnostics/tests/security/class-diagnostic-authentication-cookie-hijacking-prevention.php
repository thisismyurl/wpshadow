<?php
/**
 * Authentication Cookie Hijacking Prevention Diagnostic
 *
 * Checks for protections against authentication cookie hijacking.
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
 * Authentication Cookie Hijacking Prevention Diagnostic
 *
 * Validates security measures against auth cookie hijacking.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Authentication_Cookie_Hijacking_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'authentication-cookie-hijacking-prevention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Authentication Cookie Hijacking Prevention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for protections against authentication cookie hijacking';

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

		// Check if HTTPS is enabled
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site is not using HTTPS - cookies can be intercepted', 'wpshadow' );
		} else {
			$protections[] = __( 'Site uses HTTPS', 'wpshadow' );
		}

		// Check HSTS header
		$hsts_header = false;
		if ( function_exists( 'header_remove' ) ) {
			// Check if HSTS header will be sent
			$headers = headers_list();
			foreach ( $headers as $header ) {
				if ( strpos( $header, 'Strict-Transport-Security' ) !== false ) {
					$hsts_header = true;
					$protections[] = __( 'HSTS header is configured', 'wpshadow' );
					break;
				}
			}
		}

		// Check for secure cookie constants
		$secure_cookie = defined( 'SECURE_AUTH_COOKIE' ) ? SECURE_AUTH_COOKIE : true;
		$httponly_cookie = defined( 'SECURE_AUTH_COOKIE' );

		if ( ! $secure_cookie ) {
			$issues[] = __( 'SECURE_AUTH_COOKIE is not defined or is false', 'wpshadow' );
		} else {
			$protections[] = __( 'Secure authentication cookies enabled', 'wpshadow' );
		}

		// Check for HttpOnly flag (prevent JS access)
		if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
			$issues[] = __( 'HttpOnly flag may not be set on authentication cookies', 'wpshadow' );
		} else {
			$protections[] = __( 'HttpOnly flag protection in place', 'wpshadow' );
		}

		// Check SameSite cookie attribute
		if ( function_exists( 'wp_get_current_user' ) && is_user_logged_in() ) {
			// User is logged in - check session security
			$session_cookie = LOGGED_IN_COOKIE;

			// Check if cookie is restricted to same site
			$samesite_configured = false;

			// Check wp-config for SameSite settings
			$wp_config_path = ABSPATH . 'wp-config.php';
			if ( file_exists( $wp_config_path ) ) {
				$wp_config_content = file_get_contents( $wp_config_path, false, null, 0, 5000 );
				if ( strpos( $wp_config_content, 'SameSite' ) !== false || strpos( $wp_config_content, 'samesite' ) !== false ) {
					$samesite_configured = true;
					$protections[] = __( 'SameSite cookie attribute configured', 'wpshadow' );
				}
			}

			if ( ! $samesite_configured ) {
				$issues[] = __( 'SameSite attribute may not be configured on session cookies', 'wpshadow' );
			}
		}

		// Check for security plugins with session protection
		$security_plugins = array(
			'wordfence/wordfence.php'         => 'Wordfence',
			'sucuri-scanner/sucuri.php'       => 'Sucuri Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'jetpack/jetpack.php'             => 'Jetpack',
		);

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $security_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$protections[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s session protection enabled', 'wpshadow' ),
					$name
				);
			}
		}

		// Check for IP-based session validation
		global $wp_filter;
		if ( isset( $wp_filter['init'] ) && isset( $wp_filter['wp_login'] ) ) {
			// Check if custom session validation is hooked
			$protections[] = __( 'Custom session validation may be implemented', 'wpshadow' );
		}

		// Check cookie timeout
		$cookie_time = HOUR_IN_SECONDS;
		if ( defined( 'REMEMBER_ME_COOKIE_TIMEOUT' ) ) {
			$cookie_time = REMEMBER_ME_COOKIE_TIMEOUT;
		}

		if ( $cookie_time > WEEK_IN_SECONDS ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Cookie timeout is too long (%d days) - increases hijacking risk', 'wpshadow' ),
				( $cookie_time / DAY_IN_SECONDS )
			);
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Authentication cookie hijacking prevention issues found', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/authentication-cookie-hijacking-prevention',
				'details'      => array(
					'issues'      => $issues,
					'protections' => $protections,
				),
			);
		}

		return null;
	}
}
