<?php
/**
 * XML-RPC Brute Force Protection Diagnostic
 *
 * Detects XML-RPC endpoints vulnerable to brute force attacks
 * via system.multicall amplification.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Brute Force Protection Diagnostic Class
 *
 * Checks for:
 * - XML-RPC interface enabled
 * - system.multicall amplification vulnerability
 * - Rate limiting on XML-RPC endpoint
 * - IP-based blocking for failed attempts
 * - Authentication method restrictions
 * - XML-RPC firewall rules
 *
 * XML-RPC's system.multicall allows attackers to attempt thousands
 * of password combinations in a single HTTP request, bypassing
 * traditional rate limiting and brute force protection.
 *
 * @since 1.2033.2108
 */
class Diagnostic_XML_RPC_Brute_Force extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'xml-rpc-brute-force';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'XML-RPC Brute Force Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects XML-RPC brute force and amplification vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates XML-RPC security configuration.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Is XML-RPC enabled?
		$xmlrpc_enabled = self::is_xmlrpc_enabled();
		if ( ! $xmlrpc_enabled ) {
			// XML-RPC disabled, no vulnerability.
			return null;
		}

		$issues[] = __( 'XML-RPC interface is enabled', 'wpshadow' );

		// Check 2: Is system.multicall restricted?
		$multicall_restricted = self::is_multicall_restricted();
		if ( ! $multicall_restricted ) {
			$issues[] = __( 'XML-RPC system.multicall amplification attack possible (100x-1000x multiplier)', 'wpshadow' );
		}

		// Check 3: Check for rate limiting.
		$has_rate_limit = self::check_xmlrpc_rate_limiting();
		if ( ! $has_rate_limit ) {
			$issues[] = __( 'No rate limiting detected on XML-RPC endpoint', 'wpshadow' );
		}

		// Check 4: Check authentication method filtering.
		$filters_methods = self::check_authentication_method_filtering();
		if ( ! $filters_methods ) {
			$issues[] = __( 'XML-RPC does not filter authentication methods (wp.getUsersBlogs risk)', 'wpshadow' );
		}

		// Check 5: Check for .htaccess protection.
		$has_htaccess_block = self::check_htaccess_xmlrpc_block();
		if ( ! $has_htaccess_block ) {
			$issues[] = __( 'XML-RPC not blocked at .htaccess level', 'wpshadow' );
		}

		// Check 6: Check for IP blocking on failed attempts.
		$has_ip_blocking = self::check_ip_blocking();
		if ( ! $has_ip_blocking ) {
			$issues[] = __( 'No IP-based blocking for repeated XML-RPC authentication failures', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d XML-RPC security issue detected',
						'%d XML-RPC security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xml-rpc-brute-force',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'XML-RPC brute force attacks are devastating because system.multicall allows 1000+ login attempts in a single request. ' .
						'Attackers can test thousands of username/password combinations while appearing as a single HTTP request, ' .
						'completely bypassing standard rate limiting and login attempt monitors. According to Sucuri, 95% of WordPress ' .
						'brute force attacks in 2020 targeted XML-RPC. The attack is stealthy - one HTTP request can contain 1000 login ' .
						'attempts, making it nearly impossible to detect with standard web application firewalls. Once credentials are ' .
						'compromised, attackers gain full site access. XML-RPC also enables DDoS amplification attacks via pingback functionality.',
						'wpshadow'
					),
					'recommendation' => __(
						'Best solution: Disable XML-RPC entirely if not needed (filter xmlrpc_enabled). If required for Jetpack/mobile apps, ' .
						'filter xmlrpc_methods to remove system.multicall and wp.getUsersBlogs. Add .htaccess rules to block XML-RPC by IP whitelist. ' .
						'Implement aggressive rate limiting (max 5 requests/minute per IP). Use application passwords instead of admin credentials. ' .
						'Monitor xmlrpc.php access in logs. Consider Cloudflare or Wordfence firewall rules. Alternative: Require authentication tokens instead of passwords.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'brute-force-protection',
				'xmlrpc-guide'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Check if XML-RPC is enabled.
	 *
	 * @since  1.2033.2108
	 * @return bool True if enabled.
	 */
	private static function is_xmlrpc_enabled() {
		// Check if XML-RPC is disabled by filter.
		$enabled = apply_filters( 'xmlrpc_enabled', true );
		
		if ( ! $enabled ) {
			return false;
		}

		// Check if xmlrpc.php file exists and is accessible.
		$xmlrpc_file = ABSPATH . 'xmlrpc.php';
		if ( ! file_exists( $xmlrpc_file ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if system.multicall is restricted.
	 *
	 * @since  1.2033.2108
	 * @return bool True if restricted.
	 */
	private static function is_multicall_restricted() {
		// Check if xmlrpc_methods filter removes system.multicall.
		$methods = apply_filters( 'xmlrpc_methods', array() );
		
		// If system.multicall is present or no filtering applied, it's vulnerable.
		if ( isset( $methods['system.multicall'] ) || empty( $methods ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check for rate limiting implementation.
	 *
	 * @since  1.2033.2108
	 * @return bool True if rate limiting found.
	 */
	private static function check_xmlrpc_rate_limiting() {
		// Check for common rate limiting filters/actions.
		$has_filters = has_filter( 'xmlrpc_before_insert_post' ) ||
		               has_filter( 'authenticate' ) ||
		               has_action( 'xmlrpc_call' );

		if ( ! $has_filters ) {
			return false;
		}

		// Check for rate limiting plugins.
		$rate_limit_plugins = array(
			'limit-login-attempts',
			'wordfence',
			'all-in-one-wp-security',
			'ithemes-security',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $rate_limit_plugins as $plugin ) {
			foreach ( $active_plugins as $active ) {
				if ( str_contains( $active, $plugin ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check authentication method filtering.
	 *
	 * @since  1.2033.2108
	 * @return bool True if filtering found.
	 */
	private static function check_authentication_method_filtering() {
		// Check if xmlrpc_methods filter is being used.
		return has_filter( 'xmlrpc_methods' );
	}

	/**
	 * Check .htaccess for XML-RPC blocking.
	 *
	 * @since  1.2033.2108
	 * @return bool True if blocked.
	 */
	private static function check_htaccess_xmlrpc_block() {
		$htaccess_file = ABSPATH . '.htaccess';
		
		if ( ! file_exists( $htaccess_file ) || ! is_readable( $htaccess_file ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $htaccess_file );
		
		// Check for XML-RPC blocking rules.
		return str_contains( $content, 'xmlrpc.php' ) && 
		       ( str_contains( $content, 'deny' ) || str_contains( $content, 'Forbidden' ) );
	}

	/**
	 * Check for IP blocking implementation.
	 *
	 * @since  1.2033.2108
	 * @return bool True if IP blocking found.
	 */
	private static function check_ip_blocking() {
		// Check for fail2ban-style logging or IP blocking hooks.
		return has_action( 'wp_login_failed' ) || has_action( 'xmlrpc_login_error' );
	}
}
