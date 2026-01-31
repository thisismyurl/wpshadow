<?php
/**
 * Plugin Backdoor Installation Risk Diagnostic
 *
 * Detects plugins vulnerable to backdoor installation.
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
 * Diagnostic_Plugin_Backdoor_Installation_Risk Class
 *
 * Identifies plugins vulnerable to backdoor installation.
 */
class Diagnostic_Plugin_Backdoor_Installation_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-backdoor-installation-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Backdoor Installation Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to backdoor installation';

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
		$backdoor_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for file write operations without validation
			if ( preg_match( '/file_put_contents|fopen.*[wr]|fwrite/', $content ) ) {
				// Check if file path is validated
				if ( ! preg_match( '/realpath|dirname|basename|wp_upload_dir|wp_plugin_dir/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Writes files without path validation (could create backdoor).', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for plugin/theme installation without signature verification
			if ( preg_match( '/wp_install_plugin|wp_install_theme|install_plugin_form/', $content ) ) {
				if ( ! preg_match( '/verify_plugin_package|check_package_plugin|wp_remote_get.*signature/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Installs plugins/themes without signature verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for remote execution via include/require
			if ( preg_match( '/(?:include|require).*\$_(?:GET|POST|REQUEST|SERVER|COOKIE)/', $content ) ) {
				$backdoor_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Includes files based on user input (Remote Code Execution/backdoor).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for creating admin users remotely
			if ( preg_match( '/wp_create_user|wp_insert_user.*password.*email/', $content ) ) {
				// Check if triggered by user action without verification
				if ( ! preg_match( '/is_admin|current_user_can|wp_verify_nonce/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: May create admin users without proper verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for web shell patterns
			if ( preg_match( '/system\s*\(\s*\$_[^)]*\)|shell_exec\s*\(\s*\$_|exec\s*\(\s*\$_/', $content ) ) {
				$backdoor_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Contains web shell patterns (backdoor).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $backdoor_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d potential backdoor installation risks detected: %s', 'wpshadow' ),
					count( $backdoor_concerns ),
					implode( ' | ', array_slice( $backdoor_concerns, 0, 2 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'backdoor_concerns' => $backdoor_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/backdoor-prevention',
			);
		}

		return null;
	}
}
