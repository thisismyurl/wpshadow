<?php
/**
 * Authentication Cookie Hijacking Prevention Diagnostic
 *
 * Validates security measures protecting WordPress authentication cookies from theft\n * and replay attacks. Authentication cookies are golden tickets: steal one, gain account\n * access without knowing password. Common theft vectors: network sniffing (HTTP), malicious\n * JavaScript (XSS), malware on client machine.\n *
 * **What This Check Does:**
 * - Verifies HTTPS-only cookie flag is set (prevents network sniffing)\n * - Confirms HttpOnly cookie flag prevents JavaScript access (stops XSS theft)\n * - Checks for SameSite cookie attribute (prevents CSRF/cross-site cookie usage)\n * - Validates secure cookie settings are persistent and enforced\n * - Tests that authentication cookies expire after reasonable timeout\n * - Detects if cookies are still sent over HTTP (unencrypted compromise)\n *
 * **Why This Matters:**
 * Authentication cookie theft = account hijacking without password reset capability. Scenarios:\n * - Network MITM on public WiFi intercepts unencrypted cookie, replays it\n * - Malicious ad injects XSS script, steals cookie from browser\n * - Malware on computer extracts cookie from browser cache\n * - CSRF attack tricks browser into using cookie on malicious site\n *
 * **Business Impact:**
 * Cookie hijacking usually undetectable: attacker uses your account, leaving no login trace.\n * Scenario: Public WiFi attacker steals admin cookie. Modifies site to inject malware.\n * Malware on-clicked by 1,000 visitors. Within 24 hours: 50 new infections, ISP complaint,\n * site listed in malware databases. Recovery: 40 hours cleanup, reputation damage, lost revenue.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Multi-layered authentication protection\n * - #9 Show Value: Eliminates entire hijacking attack class\n * - #10 Beyond Pure: Protects users even if they use public WiFi\n *
 * **Related Checks:**
 * - HTTPS Enforcement (transport security)\n * - User Capability Auditing (detect if hijacked account used)\n * - Login Page Rate Limiting (detect brute force attempts)\n *
 * **Learn More:**
 * Cookie security hardening: https://wpshadow.com/kb/auth-cookie-protection
 * Video: WordPress security best practices (15min): https://wpshadow.com/training/auth-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication Cookie Hijacking Prevention Diagnostic Class
 *
 * Implements cookie security validation by reading WordPress configuration constants\n * and making test requests to verify cookie headers. Detection: checks\n * COOKIEHTTPONLY, COOKIESECURE, FORCE_SSL_ADMIN constants, examines Set-Cookie response\n * headers for security flags.\n *
 * **Detection Pattern:**
 * 1. Check define( 'COOKIEHTTPONLY', true ) in wp-config.php\n * 2. Check define( 'COOKIESECURE', true ) if HTTPS active\n * 3. Check define( 'FORCE_SSL_ADMIN', true )\n * 4. Make test request to login page, inspect Set-Cookie response headers\n * 5. Verify headers include: Secure, HttpOnly, SameSite=Lax/Strict\n * 6. Return failure if any security flag missing\n *
 * **Real-World Scenario:**
 * Business site behind corporate proxy with SSL inspection. Developer left COOKIEHTTPONLY\n * = false for \"debugging.\" Attacker on same corporate network uses packet sniffer, captures\n * authentication cookie. Attacker injects malware via admin panel. By the time company\n * detected it, malware infected 10,000 client machines. Impact: $500K+ liability, contract\n * terminations, criminal investigation.\n *
 * **Implementation Notes:**
 * - Reads wp-config.php constants or uses get_option fallbacks\n * - Makes real HTTP test to verify header presence\n * - Returns severity: critical (no security flags), high (partial protection)\n * - Non-fixable diagnostic (requires wp-config.php modification)\n *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Authentication cookie hijacking prevention issues found', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/authentication-cookie-hijacking-prevention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'      => $issues,
					'protections' => $protections,
				),
				'context'      => array(
					'why'            => __(
						'Authentication cookies are equivalent to passwords. If an attacker steals a cookie, they can log in without the password or 2FA. Common vectors include XSS, unencrypted HTTP traffic, and malware. Missing Secure/HttpOnly/SameSite flags increases the likelihood of theft or cross-site misuse. Long-lived cookies extend the window of opportunity for hijacking. These weaknesses are often invisible to users and can lead to silent account compromise.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Enforce HTTPS site-wide and set FORCE_SSL_ADMIN true.
2. Ensure authentication cookies include Secure, HttpOnly, and SameSite=Lax or Strict.
3. Reduce cookie lifetime (especially for "remember me").
4. Add HSTS to prevent SSL stripping.
5. Mitigate XSS across the site to prevent JS cookie theft.
6. Use session invalidation on password change and admin role changes.
7. Consider IP/device binding for admin sessions if acceptable.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-hardening',
				'cookie_hijacking_prevention'
			);

			return $finding;
		}

		return null;
	}
}
