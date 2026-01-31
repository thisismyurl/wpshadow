<?php
/**
 * Plugin Authentication Bypass Risk Diagnostic
 *
 * Detects plugins with weak authentication handling.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Authentication_Bypass_Risk Class
 *
 * Identifies plugins vulnerable to authentication bypass.
 */
class Diagnostic_Plugin_Authentication_Bypass_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-authentication-bypass-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Authentication Bypass Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins with weak authentication handling';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$auth_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for AJAX handlers accessible to non-authenticated users
			if ( preg_match( '/add_action\s*\(\s*["\']wp_ajax_nopriv_/', $content ) ) {
				// Check if they verify capability
				if ( ! preg_match( '/current_user_can|is_user_logged_in/', $content ) ) {
					$auth_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: AJAX handlers accessible to non-authenticated users without capability check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for public-facing endpoints without authentication
			if ( preg_match( '/if\s*\(\s*!?isset\s*\(\s*\$_GET\[/', $content ) ) {
				// Check for early return if not authenticated
				if ( ! preg_match( '/is_user_logged_in|current_user_can|wp_die.*permission/', $content ) ) {
					$auth_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: May expose data via $_GET without authentication check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for REST API endpoints without proper authentication
			if ( preg_match( '/register_rest_route.*permission_callback.*__return_true/', $content ) ) {
				$auth_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: REST API endpoint with permission_callback returning true (public).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for usage of 'action' parameter without verification (plugin.php inclusion)
			if ( preg_match( '/\$_REQUEST\[["\']action["\']/', $content ) ) {
				// Check if it verifies user
				if ( ! preg_match( '/is_user_logged_in|current_user_can/', $content ) ) {
					$auth_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Uses action parameter without user authentication.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for hardcoded credentials
			if ( preg_match( '/password\s*=\s*["\'][^\'"]+["\']|api_key\s*=\s*["\'][^\'"]+["\']/', $content ) ) {
				$auth_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Contains hardcoded credentials or API keys.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $auth_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d authentication bypass risks detected: %s', 'wpshadow' ),
					count( $auth_risks ),
					implode( ' | ', array_slice( $auth_risks, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'auth_risks' => $auth_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/authentication-security',
			);
		}

		return null;
	}
}
