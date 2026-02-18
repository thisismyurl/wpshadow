<?php
/**
 * Plugin Update Success Verification Diagnostic
 *
 * Verifies that plugin updates completed successfully without errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Success Verification Diagnostic Class
 *
 * Checks for failed plugin updates or incomplete update processes.
 *
 * @since 1.5049.1315
 */
class Diagnostic_Plugin_Update_Success_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-success-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Success Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for failed or incomplete plugin updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$problematic_plugins = array();

		// Check for update_plugins transient.
		$update_plugins = get_site_transient( 'update_plugins' );

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for plugins with available updates.
		if ( ! empty( $update_plugins->response ) ) {
			foreach ( $update_plugins->response as $plugin_file => $plugin_data ) {
				// Check if plugin file exists.
				if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
					$problematic_plugins[] = array(
						'plugin' => $plugin_file,
						'issue'  => 'Plugin file missing after update',
					);
				}
			}
		}

		// Check for plugins that are active but broken.
		foreach ( $active_plugins as $plugin_file ) {
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				$problematic_plugins[] = array(
					'plugin' => $plugin_file,
					'issue'  => 'Active plugin file missing',
				);
			}
		}

		// Check for .maintenance file (stuck update).
		if ( file_exists( ABSPATH . '.maintenance' ) ) {
			$issues[] = __( 'Site in maintenance mode (update may be stuck)', 'wpshadow' );
		}

		// Check for update errors in options.
		$update_errors = get_option( 'wpshadow_plugin_update_errors', array() );
		if ( ! empty( $update_errors ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of update errors */
				_n(
					'%d plugin update error recorded',
					'%d plugin update errors recorded',
					count( $update_errors ),
					'wpshadow'
				),
				count( $update_errors )
			);
		}

		// Check for partial plugin directories (incomplete updates).
		$plugin_dir = WP_PLUGIN_DIR;
		$plugin_folders = glob( $plugin_dir . '/*', GLOB_ONLYDIR );

		foreach ( $plugin_folders as $folder ) {
			$folder_name = basename( $folder );

			// Skip if it's a known plugin.
			$has_main_file = false;
			foreach ( $all_plugins as $plugin_file => $plugin_data ) {
				if ( strpos( $plugin_file, $folder_name . '/' ) === 0 ) {
					$has_main_file = true;
					break;
				}
			}

			// Check for .tmp or .backup folders (incomplete updates).
			if ( ! $has_main_file && ( strpos( $folder_name, '.tmp' ) !== false || strpos( $folder_name, '.backup' ) !== false ) ) {
				$issues[] = sprintf(
					/* translators: %s: folder name */
					__( 'Incomplete update folder found: %s', 'wpshadow' ),
					$folder_name
				);
			}
		}

		if ( ! empty( $issues ) || ! empty( $problematic_plugins ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugin updates may have failed or incomplete', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'details'     => array(
					'problematic_plugins' => $problematic_plugins,
					'issues'              => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-update-success-verification',
			);
		}

		return null;
	}
}
