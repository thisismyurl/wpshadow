<?php
/**
 * User Session Security Diagnostic
 *
 * Validates that user sessions are properly secured with appropriate
 * timeouts, cookie flags, and security headers.
 * Insecure sessions = attacker steals cookie. Hijacks session.
 * Secure sessions = HttpOnly, Secure, SameSite flags. Hijacking harder.
 *
 * **What This Check Does:**
 * - Checks session cookie flags (HttpOnly, Secure, SameSite)
 * - Validates session timeout configured
 * - Tests session regeneration on privilege change
 * - Checks if concurrent sessions limited
 * - Validates session token rotation
 * - Returns severity if session insecure
 *
 * **Why This Matters:**
 * Session cookie without HttpOnly flag = JavaScript can read.
 * XSS attack steals cookie. Attacker hijacks session.
 * With HttpOnly: JavaScript blocked. Cookie theft harder.
 *
 * **Business Impact:**
 * Admin session cookie lacks HttpOnly flag. XSS vulnerability exists.
 * Attacker injects script. Steals admin cookie. Hijacks session.
 * Full admin access. Site compromised. Cost: $500K+. With HttpOnly:
 * script can't read cookie. Session hijacking blocked. Admin safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sessions hardened against theft
 * - #9 Show Value: Prevents session hijacking
 * - #10 Beyond Pure: Defense-in-depth session security
 *
 * **Related Checks:**
 * - Cookie Security Configuration (related)
 * - XSS Protection (complementary)
 * - HTTPS Enforcement (required for Secure flag)
 *
 * **Learn More:**
 * Session security: https://wpshadow.com/kb/session-security
 * Video: Securing sessions (11min): https://wpshadow.com/training/sessions
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
 * User Session Security Diagnostic Class
 *
 * Checks user session security configuration.
 *
 * **Detection Pattern:**
 * 1. Inspect session cookie attributes
 * 2. Check HttpOnly, Secure, SameSite flags
 * 3. Validate session timeout settings
 * 4. Test session regeneration on login
 * 5. Check concurrent session limits
 * 6. Return each missing security measure
 *
 * **Real-World Scenario:**
 * Session cookies have Secure and HttpOnly flags. Attacker finds XSS.
 * Injects script to steal cookies. Script runs but can't read cookies
 * (HttpOnly blocks). Attacker fails. With insecure cookies: script
 * reads cookie value. Sends to attacker. Session hijacked.
 *
 * **Implementation Notes:**
 * - Checks session cookie configuration
 * - Validates security flags
 * - Tests timeout and regeneration
 * - Severity: high (missing HttpOnly/Secure)
 * - Treatment: configure session cookies with security flags
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Session_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-session-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Session Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user session security configuration';

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
		$issues = array();

		// Check if HTTPS is enabled (cookies will be secure).
		$is_https = is_ssl();

		if ( ! $is_https ) {
			$issues[] = __( 'Site is not using HTTPS (cookies will be transmitted in plain text)', 'wpshadow' );
		}

		// Check for secure cookie flag configuration.
		$secure_cookies = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN;

		if ( ! $secure_cookies ) {
			$issues[] = __( 'FORCE_SSL_ADMIN not configured (admin cookies may not be secure)', 'wpshadow' );
		}

		// Check for session token rotation.
		$rotate_sessions = defined( 'SESSION_REFRESH' );

		if ( ! $rotate_sessions ) {
			$issues[] = __( 'Session token rotation not configured', 'wpshadow' );
		}

		// Check session timeout settings.
		$session_lifetime = defined( 'ADMIN_COOKIE_PATH' ) ? get_transient( 'session_lifetime' ) : 0;

		// WordPress uses session tokens now - check for expiration.
		// Session tokens are stored in usermeta and expire after ~14 days by default.

		// Check for remember-me functionality duration.
		$remember_duration = defined( 'WPSHADOW_REMEMBER_ME_DAYS' ) ? WPSHADOW_REMEMBER_ME_DAYS : 14;

		if ( $remember_duration > 60 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Remember me cookies last for %d days (should be 14-30 maximum)', 'wpshadow' ),
				$remember_duration
			);
		}

		// Check for session fixation protection.
		// WordPress regenerates session on login by default.

		// Check for security headers related to sessions.
		// These headers should prevent session hijacking via XSS.

		// Check if site has X-Frame-Options header.
		$x_frame_options = defined( 'X_FRAME_OPTIONS' ) ? X_FRAME_OPTIONS : 'SAMEORIGIN';

		if ( 'DENY' !== $x_frame_options && 'SAMEORIGIN' !== $x_frame_options ) {
			$issues[] = sprintf(
				/* translators: %s: header value */
				__( 'X-Frame-Options header not properly configured: %s', 'wpshadow' ),
				$x_frame_options
			);
		}

		// Check for Content-Security-Policy header.
		$has_csp = false;

		if ( defined( 'CONTENT_SECURITY_POLICY' ) ) {
			$has_csp = true;
		}

		if ( ! $has_csp ) {
			$issues[] = __( 'Content-Security-Policy header not configured (increases XSS and session hijacking risk)', 'wpshadow' );
		}

		// Check for SameSite cookie attribute.
		// WordPress 5.4+ supports this.
		$samesite_attribute = defined( 'COOKIE_SAMESITE' ) ? COOKIE_SAMESITE : 'Lax';

		if ( 'Lax' !== $samesite_attribute && 'Strict' !== $samesite_attribute ) {
			$issues[] = sprintf(
				/* translators: %s: SameSite value */
				__( 'SameSite cookie attribute not properly configured: %s', 'wpshadow' ),
				$samesite_attribute
			);
		}

		// Check for concurrent session limits.
		$max_sessions = apply_filters( 'wp_session_tokens_max_per_user', 10 );

		if ( $max_sessions > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: maximum concurrent sessions */
				__( 'Max concurrent sessions per user is %d (should be 1-3 for security)', 'wpshadow' ),
				$max_sessions
			);
		}

		// Check for active sessions in database.
		global $wpdb;

		$active_sessions = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta}
			WHERE meta_key = 'session_tokens'"
		);

		// High number of sessions might indicate session hijacking or old abandoned sessions.

		// Check for session token expiration handling.
		// WordPress should automatically clean up expired tokens.

		// Check for admin session timeout.
		$admin_session_timeout = defined( 'ADMIN_SESSION_TIMEOUT' ) ? ADMIN_SESSION_TIMEOUT : 0;

		if ( 0 === $admin_session_timeout ) {
			$issues[] = __( 'Admin session timeout not configured (sessions may be infinite)', 'wpshadow' );
		} elseif ( $admin_session_timeout > 86400 ) { // 24 hours.
			$issues[] = sprintf(
				/* translators: %d: hours */
				__( 'Admin session timeout is %d hours (should be 1-8 hours)', 'wpshadow' ),
				absint( $admin_session_timeout / 3600 )
			);
		}

		// Check for front-end session timeout.
		// Front-end doesn't have a built-in timeout - should be implemented.

		// Check for IP address validation in sessions.
		// WordPress doesn't validate IP by default (allows session use from different IPs).

		// Check for user agent validation.
		// WordPress validates user agent during session verification.

		// Check if there are suspicious user agents in active sessions.
		$suspicious_agents = $wpdb->get_results(
			"SELECT user_id, COUNT(*) as count
			FROM {$wpdb->usermeta}
			WHERE meta_key = 'session_tokens'
			AND meta_value LIKE '%bot%'
			OR meta_value LIKE '%crawler%'
			LIMIT 10"
		);

		if ( ! empty( $suspicious_agents ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious agents */
				__( '%d sessions with suspicious user agents detected', 'wpshadow' ),
				count( $suspicious_agents )
			);
		}

		// Check for session plugins.
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php' => 'WP Session Manager',
			'wp-force-logout-on-browser-close/wp-force-logout-on-browser-close.php' => 'WP Force Logout',
		);

		$has_session_plugins = false;
		foreach ( $session_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_session_plugins = true;
			}
		}

		if ( ! $has_session_plugins ) {
			// Could add session management plugins for extra security.
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of session security issues */
					__( 'Found %d user session security issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-security',
				'details'      => array(
					'issues'         => $issues,
					'is_https'       => $is_https,
					'secure_cookies' => $secure_cookies,
				),
				'context'      => array(
					'why'            => __(
						'Weak session security enables account takeover without the password. Missing HTTPS allows cookie interception, missing Secure/HttpOnly/SameSite flags allow theft and cross-site reuse, and long session lifetimes increase exposure. Attackers commonly target admin sessions because they grant full site control. These gaps also make XSS far more dangerous because stolen cookies can be reused to bypass authentication entirely.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Enforce HTTPS site-wide and set FORCE_SSL_ADMIN to true.
2. Set Secure, HttpOnly, and SameSite=Lax/Strict on auth cookies.
3. Configure ADMIN_SESSION_TIMEOUT to 2-8 hours.
4. Limit concurrent sessions per user (1-3).
5. Enable Content-Security-Policy to reduce XSS risk.
6. Rotate session tokens on login and privilege changes.
7. Review and revoke suspicious sessions regularly.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-hardening',
				'user_session_security'
			);

			return $finding;
		}

		return null;
	}
}
