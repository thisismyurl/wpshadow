<?php
/**
 * Plugin Privilege Escalation Risk Diagnostic
 *
 * Detects plugins allowing privilege escalation.
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
 * Diagnostic_Plugin_Privilege_Escalation_Risk Class
 *
 * Identifies plugins vulnerable to privilege escalation.
 */
class Diagnostic_Plugin_Privilege_Escalation_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-privilege-escalation-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Privilege Escalation Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to privilege escalation attacks';

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
		$privesc_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for weak capability checks
			if ( preg_match( '/current_user_can\s*\(\s*["\'](?:read|view)["\']/', $content ) ) {
				$privesc_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses weak capability checks (read/view instead of manage_options).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for admin pages without capability check
			if ( preg_match( '/add_menu_page|add_submenu_page/', $content ) ) {
				// Verify it checks capability
				if ( ! preg_match( '/current_user_can\s*\(\s*["\']manage_options["\']/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Admin menu page without manage_options capability check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for updating user roles without verification
			if ( preg_match( '/wp_update_user|update_user_meta.*role/', $content ) ) {
				// Check if it verifies capability
				if ( ! preg_match( '/current_user_can|is_user_logged_in/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Updates user roles without proper verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for file inclusion with user input
			if ( preg_match( '/include|require|include_once|require_once.*\$_(?:GET|POST|REQUEST)/', $content ) ) {
				$privesc_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Includes files based on user input (Remote Code Execution risk).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for updating options without admin check
			if ( preg_match( '/update_option\s*\(\s*["\']siteurl["\']|update_option\s*\(\s*["\']home["\']/', $content ) ) {
				// Check if it verifies capability
				if ( ! preg_match( '/current_user_can\s*\(\s*["\']manage_options["\']/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Updates critical site options without capability check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}
		}

		if ( ! empty( $privesc_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d privilege escalation risks detected: %s', 'wpshadow' ),
					count( $privesc_risks ),
					implode( ' | ', array_slice( $privesc_risks, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'privesc_risks' => $privesc_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/privilege-escalation-prevention',
			);
		}

		return null;
	}
}
