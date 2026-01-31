<?php
/**
 * Login Attempt Rate Limiting Diagnostic
 *
 * Detects if login attempts are rate-limited to prevent brute force attacks.
 * Checks for active protection via common plugins and server-level protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6027.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Rate Limiting Diagnostic Class
 *
 * Detects whether WordPress login attempts are rate-limited to prevent
 * brute force attacks. Default WordPress has no rate limiting, making it
 * vulnerable to automated password guessing.
 *
 * @since 1.6027.1445
 */
class Diagnostic_Login_Rate_Limiting extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-rate-limiting';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Attempt Rate Limiting';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies login attempts are rate-limited to prevent brute force attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Known rate limiting plugins
	 *
	 * @var array<string, array{name: string, function: string}>
	 */
	private const RATE_LIMIT_PLUGINS = array(
		'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => array(
			'name'     => 'Limit Login Attempts Reloaded',
			'function' => 'limit_login_option',
		),
		'limit-login-attempts/limit-login-attempts.php'                   => array(
			'name'     => 'Limit Login Attempts',
			'function' => 'limit_login_option',
		),
		'wordfence/wordfence.php'                                         => array(
			'name'     => 'Wordfence Security',
			'function' => 'wordfence',
		),
		'all-in-one-wp-security-and-firewall/wp-security.php'            => array(
			'name'     => 'All In One WP Security',
			'function' => 'aiowps_activate_firewall',
		),
		'better-wp-security/better-wp-security.php'                       => array(
			'name'     => 'iThemes Security',
			'function' => 'itsec_load_textdomain',
		),
		'login-lockdown/loginlockdown.php'                                => array(
			'name'     => 'Login LockDown',
			'function' => 'loginLockdown_install',
		),
		'wp-fail2ban/wp-fail2ban.php'                                     => array(
			'name'     => 'WP fail2ban',
			'function' => 'org\\lecklider\\charles\\wordpress\\wp_fail2ban\\activate',
		),
	);

	/**
	 * Guardian module detection
	 *
	 * @var array{class: string, method: string}
	 */
	private const GUARDIAN_MODULE = array(
		'class'  => 'WPShadow\\Guardian\\Login_Protection',
		'method' => 'is_enabled',
	);

	/**
	 * Run the diagnostic check
	 *
	 * Detects rate limiting via:
	 * 1. Guardian module (WPShadow's own protection)
	 * 2. Common security plugins
	 * 3. Custom filters on authenticate
	 * 4. HTTP headers from recent login attempts
	 *
	 * @since  1.6027.1445
	 * @return array|null Finding array if rate limiting not detected, null otherwise.
	 */
	public static function check() {
		// Check Guardian module first.
		$guardian_active = self::is_guardian_active();

		// Check known security plugins.
		$active_plugins = self::get_active_rate_limit_plugins();

		// Check for custom rate limiting implementations.
		$custom_limiting = self::has_custom_rate_limiting();

		// Check for server-level protection.
		$server_limiting = self::has_server_rate_limiting();

		// If any protection is found, no issue.
		if ( $guardian_active || ! empty( $active_plugins ) || $custom_limiting || $server_limiting ) {
			return null;
		}

		// Build details about missing protection.
		$details = self::build_finding_details();

		// Calculate threat level based on exposed state.
		$threat_level = self::calculate_threat_level();

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress login page has no rate limiting, allowing unlimited brute force attempts', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/security-login-rate-limiting',
			'family'       => self::$family,
			'meta'         => array(
				'protection_status' => 'none',
				'vulnerable_since'  => self::get_install_date(),
				'login_url'         => wp_login_url(),
			),
			'details'      => $details,
		);
	}

	/**
	 * Check if Guardian login protection module is active
	 *
	 * @since  1.6027.1445
	 * @return bool True if Guardian protecting logins.
	 */
	private static function is_guardian_active(): bool {
		if ( ! class_exists( self::GUARDIAN_MODULE['class'] ) ) {
			return false;
		}

		$class  = self::GUARDIAN_MODULE['class'];
		$method = self::GUARDIAN_MODULE['method'];

		if ( ! method_exists( $class, $method ) ) {
			return false;
		}

		return call_user_func( array( $class, $method ) );
	}

	/**
	 * Get list of active rate limiting plugins
	 *
	 * @since  1.6027.1445
	 * @return array<string, string> Array of active plugin names keyed by basename.
	 */
	private static function get_active_rate_limit_plugins(): array {
		$active = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::RATE_LIMIT_PLUGINS as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Verify the plugin is actually functional.
				if ( isset( $plugin_data['function'] ) && function_exists( $plugin_data['function'] ) ) {
					$active[ $plugin_file ] = $plugin_data['name'];
				} elseif ( isset( $plugin_data['function'] ) && class_exists( $plugin_data['function'] ) ) {
					$active[ $plugin_file ] = $plugin_data['name'];
				}
			}
		}

		return $active;
	}

	/**
	 * Check for custom rate limiting implementations
	 *
	 * Looks for filters on authenticate hook that might implement rate limiting.
	 *
	 * @since  1.6027.1445
	 * @return bool True if custom rate limiting detected.
	 */
	private static function has_custom_rate_limiting(): bool {
		global $wp_filter;

		if ( ! isset( $wp_filter['authenticate'] ) ) {
			return false;
		}

		// Check for authenticate filters with suspicious names.
		$filters = $wp_filter['authenticate'];

		foreach ( $filters as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function_name = '';

				if ( is_array( $callback['function'] ) ) {
					if ( is_object( $callback['function'][0] ) ) {
						$function_name = get_class( $callback['function'][0] ) . '::' . $callback['function'][1];
					} else {
						$function_name = $callback['function'][0] . '::' . $callback['function'][1];
					}
				} elseif ( is_string( $callback['function'] ) ) {
					$function_name = $callback['function'];
				}

				// Look for keywords suggesting rate limiting.
				$keywords = array( 'rate', 'limit', 'throttle', 'brute', 'lockout', 'attempt' );
				foreach ( $keywords as $keyword ) {
					if ( stripos( $function_name, $keyword ) !== false ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check for server-level rate limiting
	 *
	 * Attempts to detect rate limiting via HTTP headers or server configuration.
	 *
	 * @since  1.6027.1445
	 * @return bool True if server-level rate limiting detected.
	 */
	private static function has_server_rate_limiting(): bool {
		// Check for mod_security or fail2ban configuration.
		if ( isset( $_SERVER['HTTP_X_MOD_SECURITY'] ) ) {
			return true;
		}

		// Check for rate limiting headers.
		$headers = array( 'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'RateLimit-Limit' );
		foreach ( $headers as $header ) {
			$header_key = 'HTTP_' . str_replace( '-', '_', strtoupper( $header ) );
			if ( isset( $_SERVER[ $header_key ] ) ) {
				return true;
			}
		}

		// Check for Cloudflare rate limiting.
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) && function_exists( 'get_transient' ) ) {
			$cf_rate_limit = get_transient( 'wpshadow_cf_rate_limit' );
			if ( false !== $cf_rate_limit ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build detailed finding information
	 *
	 * @since  1.6027.1445
	 * @return array<string, mixed> Detailed finding data.
	 */
	private static function build_finding_details(): array {
		return array(
			'why_matters'          => array(
				__( 'Default WordPress has no login rate limiting, allowing unlimited password attempts', 'wpshadow' ),
				__( 'Attackers can try thousands of password combinations per minute', 'wpshadow' ),
				__( 'Weak passwords can be cracked in seconds without rate limiting', 'wpshadow' ),
				__( 'Brute force attacks consume server resources and bandwidth', 'wpshadow' ),
			),
			'attack_scenarios'     => array(
				__( 'Dictionary attack: Try common passwords against admin accounts', 'wpshadow' ),
				__( 'Credential stuffing: Use leaked passwords from other sites', 'wpshadow' ),
				__( 'Username enumeration: Discover valid usernames, then brute force', 'wpshadow' ),
				__( 'Distributed attack: Multiple IPs bypass IP-based blocks', 'wpshadow' ),
			),
			'recommended_plugins'  => array(
				array(
					'name' => 'WPShadow Guardian',
					'slug' => 'wpshadow-guardian',
					'why'  => __( 'Built-in module with intelligent rate limiting and lockout', 'wpshadow' ),
				),
				array(
					'name' => 'Limit Login Attempts Reloaded',
					'slug' => 'limit-login-attempts-reloaded',
					'why'  => __( 'Lightweight, effective, actively maintained', 'wpshadow' ),
				),
				array(
					'name' => 'Wordfence Security',
					'slug' => 'wordfence',
					'why'  => __( 'Comprehensive security with rate limiting included', 'wpshadow' ),
				),
				array(
					'name' => 'iThemes Security',
					'slug' => 'better-wp-security',
					'why'  => __( 'Multiple security features including login protection', 'wpshadow' ),
				),
			),
			'remediation_steps'    => array(
				__( '1. Install a rate limiting plugin from recommendations above', 'wpshadow' ),
				__( '2. Configure lockout thresholds (suggest: 5 attempts in 20 minutes)', 'wpshadow' ),
				__( '3. Enable IP-based blocking for repeat offenders', 'wpshadow' ),
				__( '4. Consider 2FA for administrator accounts', 'wpshadow' ),
				__( '5. Monitor failed login attempts in Activity Logger', 'wpshadow' ),
			),
			'configuration_tips'   => array(
				__( 'Allow 3-5 failed attempts before lockout', 'wpshadow' ),
				__( 'Lock out for 20-60 minutes (balance security vs usability)', 'wpshadow' ),
				__( 'Whitelist your office IP if you have a static IP', 'wpshadow' ),
				__( 'Enable notifications for lockouts to detect attacks', 'wpshadow' ),
			),
			'false_positive_risks' => array(
				__( 'Users genuinely forgetting passwords may be locked out', 'wpshadow' ),
				__( 'Shared IPs (corporate networks) can trigger premature lockouts', 'wpshadow' ),
				__( 'Testing/staging environments need whitelist configurations', 'wpshadow' ),
			),
		);
	}

	/**
	 * Calculate threat level based on site exposure
	 *
	 * Higher traffic sites are more attractive targets.
	 *
	 * @since  1.6027.1445
	 * @return int Threat level 50-75.
	 */
	private static function calculate_threat_level(): int {
		// Base threat for no rate limiting.
		$threat_level = 60;

		// Increase threat if site is publicly accessible.
		if ( ! self::is_development_site() ) {
			$threat_level += 5;
		}

		// Increase threat if multiple users exist.
		$user_count = count_users();
		if ( isset( $user_count['total_users'] ) && $user_count['total_users'] > 5 ) {
			$threat_level += 5;
		}

		// Increase threat if WooCommerce or membership plugins active.
		if ( self::has_ecommerce_or_membership() ) {
			$threat_level += 5;
		}

		return min( $threat_level, 75 );
	}

	/**
	 * Check if site is in development environment
	 *
	 * @since  1.6027.1445
	 * @return bool True if development site.
	 */
	private static function is_development_site(): bool {
		// Check common development indicators.
		$dev_indicators = array(
			'localhost',
			'127.0.0.1',
			'.local',
			'.test',
			'.dev',
			'staging',
			'dev.',
		);

		$site_url = get_site_url();

		foreach ( $dev_indicators as $indicator ) {
			if ( stripos( $site_url, $indicator ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if eCommerce or membership plugins are active
	 *
	 * @since  1.6027.1445
	 * @return bool True if sensitive plugins detected.
	 */
	private static function has_ecommerce_or_membership(): bool {
		// Common eCommerce and membership plugin functions/classes.
		$indicators = array(
			'WC'                => 'WooCommerce',
			'EDD'               => 'Easy Digital Downloads',
			'edd_get_option'    => 'Easy Digital Downloads',
			'tribe_get_events'  => 'The Events Calendar',
			'pmpro_init'        => 'Paid Memberships Pro',
			'mepr_autoloader'   => 'MemberPress',
			's2member_version'  => 's2Member',
		);

		foreach ( $indicators as $indicator => $plugin_name ) {
			if ( class_exists( $indicator ) || function_exists( $indicator ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get WordPress installation date
	 *
	 * @since  1.6027.1445
	 * @return string Formatted date or 'unknown'.
	 */
	private static function get_install_date(): string {
		global $wpdb;

		$oldest_post = $wpdb->get_var(
			"SELECT post_date FROM {$wpdb->posts} ORDER BY post_date ASC LIMIT 1"
		);

		if ( $oldest_post ) {
			return gmdate( 'Y-m-d', strtotime( $oldest_post ) );
		}

		return 'unknown';
	}
}
