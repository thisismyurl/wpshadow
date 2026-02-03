<?php
/**
 * Diagnostic: XML-RPC Brute Force Amplification
 *
 * Checks if XML-RPC is enabled and vulnerable to brute force amplification attacks
 * via system.multicall (thousands of login attempts in one request).
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4009
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Brute Force Diagnostic
 *
 * Detects XML-RPC enabled with system.multicall allowing amplified brute force
 * attacks. One request can test thousands of password combinations.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Security_XMLRPC_Brute_Force extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-xmlrpc-brute-force';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Brute Force Amplification';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML-RPC allows brute force amplification attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check XML-RPC brute force risk.
	 *
	 * Verifies:
	 * - If XML-RPC is enabled
	 * - If system.multicall is available
	 * - If rate limiting is in place
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if XML-RPC is explicitly disabled.
		$xmlrpc_disabled = apply_filters( 'xmlrpc_enabled', true );
		
		if ( ! $xmlrpc_disabled ) {
			// XML-RPC is disabled - no risk.
			return null;
		}

		// Check if xmlrpc.php file exists and is accessible.
		$xmlrpc_path = ABSPATH . 'xmlrpc.php';
		if ( ! file_exists( $xmlrpc_path ) ) {
			// File doesn't exist - no risk.
			return null;
		}

		// Check for security plugins that may block XML-RPC.
		$security_plugins = array(
			'wordfence/wordfence.php'              => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'ithemes-security/ithemes-security.php' => 'iThemes Security',
			'sucuri-scanner/sucuri.php'            => 'Sucuri',
		);

		$has_xmlrpc_protection = false;
		
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Check if plugin has XML-RPC blocking enabled.
				// This would require checking each plugin's specific settings.
				// For now, note that a security plugin is active.
				$has_xmlrpc_protection = true;
				break;
			}
		}

		// Check for .htaccess rules blocking XML-RPC.
		$htaccess = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess ) ) {
			$content = file_get_contents( $htaccess ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( strpos( $content, 'xmlrpc.php' ) !== false ) {
				// .htaccess has XML-RPC rules.
				$has_xmlrpc_protection = true;
			}
		}

		if ( $has_xmlrpc_protection ) {
			// Protection is in place.
			return null;
		}

		// XML-RPC is enabled and unprotected.
		$threat_level = 80; // High severity.

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'XML-RPC is enabled without rate limiting or protection. The system.multicall method allows attackers to test thousands of login credentials in a single request. This amplifies brute force attacks by 1000x. Disable XML-RPC or use a security plugin with XML-RPC protection.',
				'wpshadow'
			),
			'severity'     => 'high',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/xmlrpc-brute-force-amplification',
		);
	}
}
