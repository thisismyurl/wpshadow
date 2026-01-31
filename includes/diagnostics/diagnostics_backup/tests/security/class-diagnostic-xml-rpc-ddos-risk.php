<?php
/**
 * XML-RPC DDoS Amplification Risk Diagnostic
 *
 * Detects if xmlrpc.php is publicly accessible without rate limiting,
 * creating a DDoS amplification vector. Attackers can use system.multicall
 * to execute hundreds of pingback requests in a single HTTP request,
 * amplifying their attack bandwidth significantly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6028.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC DDoS Amplification Risk Diagnostic Class
 *
 * Tests if xmlrpc.php accepts requests and supports pingback functionality
 * that can be abused for DDoS amplification attacks.
 *
 * @since 1.6028.1440
 */
class Diagnostic_XML_RPC_DDoS_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1440
	 * @var   string
	 */
	protected static $slug = 'xml-rpc-ddos-risk';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1440
	 * @var   string
	 */
	protected static $title = 'XML-RPC DDoS Amplification Risk';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1440
	 * @var   string
	 */
	protected static $description = 'Detects if XML-RPC is enabled without rate limiting, creating DDoS risk';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1440
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * Tests xmlrpc.php accessibility and checks for:
	 * - Whether XML-RPC is enabled
	 * - Pingback functionality available
	 * - Rate limiting configured
	 * - DDoS protection present
	 *
	 * Returns finding if XML-RPC poses a DDoS risk.
	 *
	 * @since  1.6028.1440
	 * @return array|null Null if protected, array if vulnerable.
	 */
	public static function check() {
		$xmlrpc_status = self::test_xmlrpc_status();

		if ( ! $xmlrpc_status['enabled'] ) {
			return null;
		}

		// XML-RPC enabled, but check for protections.
		if ( $xmlrpc_status['rate_limited'] || ! $xmlrpc_status['pingback_enabled'] ) {
			// Protected through rate limiting or disabled pingback.
			return null;
		}

		$threat_level = 70;
		if ( $xmlrpc_status['multicall_enabled'] ) {
			$threat_level = 80; // Higher risk with multicall.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'XML-RPC is enabled without rate limiting, creating DDoS amplification vector', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/xml-rpc-ddos-protection',
			'meta'         => array(
				'xmlrpc_url'         => site_url( 'xmlrpc.php' ),
				'pingback_enabled'   => $xmlrpc_status['pingback_enabled'],
				'multicall_enabled'  => $xmlrpc_status['multicall_enabled'],
				'rate_limited'       => $xmlrpc_status['rate_limited'],
				'immediate_actions'  => array(
					__( 'Disable XML-RPC completely if not needed', 'wpshadow' ),
					__( 'Install security plugin with XML-RPC protection', 'wpshadow' ),
					__( 'Add .htaccess rule to block xmlrpc.php', 'wpshadow' ),
					__( 'Configure rate limiting for XML-RPC endpoints', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'XML-RPC\'s system.multicall method allows attackers to send hundreds of requests in a single HTTP call. This creates massive bandwidth amplification - a 5 KB request can generate 5 MB of outbound traffic from your server. Attackers use this for DDoS amplification attacks against third parties, causing your server to participate in attacks and potentially getting your IP blacklisted.',
					'wpshadow'
				),
				'user_impact'   => __(
					'Your server may experience high CPU usage, bandwidth exhaustion, or complete unavailability during attacks. Hosting providers may suspend accounts for participating in DDoS attacks. Site performance degrades severely, users cannot access the site, and your IP may be blacklisted by major networks.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Add "deny from all" rule for xmlrpc.php in .htaccess', 'wpshadow' ),
						__( 'Use Disable XML-RPC plugin (free)', 'wpshadow' ),
						__( 'Use WPShadow auto-fix to disable XML-RPC', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Configure Wordfence rate limiting for XML-RPC', 'wpshadow' ),
						__( 'Use iThemes Security to block brute force via XML-RPC', 'wpshadow' ),
						__( 'Install Jetpack (enables alternative to XML-RPC)', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Deploy nginx rate limiting rules for /xmlrpc.php', 'wpshadow' ),
						__( 'Use Cloudflare WAF rules to rate limit XML-RPC', 'wpshadow' ),
						__( 'Implement fail2ban rules for XML-RPC abuse', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Disable XML-RPC unless you need it (most sites don\'t)', 'wpshadow' ),
					__( 'If needed, use authentication for all XML-RPC requests', 'wpshadow' ),
					__( 'Implement aggressive rate limiting (10 requests/minute)', 'wpshadow' ),
					__( 'Block system.multicall method specifically', 'wpshadow' ),
					__( 'Monitor server logs for XML-RPC abuse patterns', 'wpshadow' ),
					__( 'Use Jetpack for WordPress.com integration instead', 'wpshadow' ),
					__( 'Consider blocking /xmlrpc.php at firewall level', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Test: curl -X POST yoursite.com/xmlrpc.php', 'wpshadow' ),
					__( 'Should return 403 Forbidden or connection refused', 'wpshadow' ),
					__( 'Verify XML-RPC disabled in site settings or plugin', 'wpshadow' ),
					__( 'Check .htaccess contains XML-RPC blocking rule', 'wpshadow' ),
					__( 'Monitor server logs for xmlrpc.php requests', 'wpshadow' ),
					__( 'Use security scanner to verify XML-RPC protection', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Test XML-RPC status
	 *
	 * Checks if XML-RPC is enabled, supports pingback, and has rate limiting.
	 * Uses internal WordPress functions first, falls back to HTTP test.
	 *
	 * @since  1.6028.1440
	 * @return array XML-RPC status information.
	 */
	private static function test_xmlrpc_status() {
		$status = array(
			'enabled'           => false,
			'pingback_enabled'  => false,
			'multicall_enabled' => false,
			'rate_limited'      => false,
		);

		// Check if XML-RPC is disabled via filter.
		if ( apply_filters( 'xmlrpc_enabled', true ) === false ) {
			return $status; // XML-RPC completely disabled.
		}

		// Check if XML-RPC file exists.
		$xmlrpc_file = ABSPATH . 'xmlrpc.php';
		if ( ! file_exists( $xmlrpc_file ) ) {
			return $status;
		}

		// Test HTTP accessibility.
		$xmlrpc_url = site_url( 'xmlrpc.php' );
		$response   = wp_remote_post(
			$xmlrpc_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
				'body'      => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName><params></params></methodCall>',
				'headers'   => array(
					'Content-Type' => 'text/xml',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $status; // Cannot connect, likely blocked.
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return $status; // Not accessible.
		}

		$body = wp_remote_retrieve_body( $response );

		// XML-RPC is enabled and accessible.
		$status['enabled'] = true;

		// Check if pingback.ping method listed.
		if ( strpos( $body, 'pingback.ping' ) !== false ) {
			$status['pingback_enabled'] = true;
		}

		// Check if system.multicall listed (amplification method).
		if ( strpos( $body, 'system.multicall' ) !== false ) {
			$status['multicall_enabled'] = true;
		}

		// Check for rate limiting (presence of security plugin).
		$status['rate_limited'] = self::detect_rate_limiting();

		return $status;
	}

	/**
	 * Detect rate limiting
	 *
	 * Checks if common security plugins with XML-RPC rate limiting
	 * are active and configured.
	 *
	 * @since  1.6028.1440
	 * @return bool True if rate limiting detected.
	 */
	private static function detect_rate_limiting() {
		// Check for Wordfence.
		if ( class_exists( 'wordfence' ) ) {
			// Wordfence has XML-RPC protection.
			return true;
		}

		// Check for iThemes Security.
		if ( class_exists( 'ITSEC_Core' ) ) {
			return true;
		}

		// Check for All In One WP Security.
		if ( class_exists( 'AIO_WP_Security' ) ) {
			return true;
		}

		// Check for custom XML-RPC filter.
		if ( has_filter( 'xmlrpc_enabled' ) && apply_filters( 'xmlrpc_enabled', true ) === false ) {
			return true;
		}

		return false;
	}
}
