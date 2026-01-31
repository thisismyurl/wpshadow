<?php
/**
 * Diagnostic: XML-RPC Enabled (Brute Force Attack Vector)
 *
 * Detects if XML-RPC endpoint is enabled, allowing brute force attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Xmlrpc_Enabled
 *
 * Checks if XML-RPC is enabled, which can be exploited for brute force attacks
 * and DDoS amplification. Most modern sites don't need XML-RPC functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Xmlrpc_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'xmlrpc-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Enabled (Brute Force Attack Vector)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if XML-RPC endpoint is enabled, allowing brute force attacks';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if XML-RPC functionality is enabled. WordPress has XML-RPC enabled
	 * by default, which can be exploited for brute force attacks.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if XML-RPC enabled, null otherwise.
	 */
	public static function check() {
		// Check if xmlrpc_enabled filter is being used
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );

		if ( ! $xmlrpc_enabled ) {
			// XML-RPC is disabled via filter
			return null;
		}

		// Check if xmlrpc.php file exists and is accessible
		$xmlrpc_file = ABSPATH . 'xmlrpc.php';
		if ( ! file_exists( $xmlrpc_file ) ) {
			// File doesn't exist (unlikely but possible)
			return null;
		}

		// Check if there's an .htaccess rule blocking xmlrpc.php
		$htaccess_file = ABSPATH . '.htaccess';
		$htaccess_blocks = false;
		
		if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( false !== $htaccess_content && false !== strpos( $htaccess_content, 'xmlrpc.php' ) ) {
				$htaccess_blocks = true;
			}
		}

		if ( $htaccess_blocks ) {
			// .htaccess is blocking XML-RPC
			return null;
		}

		// XML-RPC is enabled and not blocked
		$description = __( 'XML-RPC is enabled and accessible. This legacy interface can be exploited for brute force attacks (testing multiple passwords in a single request) and DDoS amplification attacks. Most modern sites no longer need XML-RPC functionality.', 'wpshadow' );

		// Check if any plugins might be using XML-RPC
		$possible_dependencies = array();
		
		// Common plugins that use XML-RPC
		$xmlrpc_using_plugins = array(
			'jetpack/jetpack.php' => 'Jetpack',
			'wordpress-mobile/wordpress.php' => 'WordPress Mobile App',
			'wptouch/wptouch.php' => 'WPtouch',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $xmlrpc_using_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$possible_dependencies[] = $plugin_name;
			}
		}

		if ( ! empty( $possible_dependencies ) ) {
			$description .= ' ' . sprintf(
				/* translators: %s: comma-separated list of plugin names */
				__( 'Note: The following active plugins may use XML-RPC: %s. Verify they still function after disabling.', 'wpshadow' ),
				esc_html( implode( ', ', $possible_dependencies ) )
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 40,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/security-xmlrpc-enabled',
			'meta'        => array(
				'xmlrpc_file' => $xmlrpc_file,
				'possible_dependencies' => $possible_dependencies,
			),
		);
	}
}
