<?php
/**
 * Plugin Activation Hook Issues Diagnostic
 *
 * Detects problems with plugin activation hooks and processes.
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
 * Plugin Activation Hook Issues Diagnostic Class
 *
 * Checks for improperly implemented activation hooks.
 *
 * @since 1.5049.1315
 */
class Diagnostic_Plugin_Activation_Hook_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-activation-hook-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Activation Hook Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugin activation problems';

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

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for activation errors stored in transients/options.
		$activation_errors = get_option( 'wpshadow_plugin_activation_errors', array() );
		if ( ! empty( $activation_errors ) ) {
			foreach ( $activation_errors as $plugin => $error ) {
				$problematic_plugins[] = array(
					'plugin' => $plugin,
					'error'  => $error,
				);
			}
		}

		// Check each active plugin for common activation issues.
		foreach ( $active_plugins as $plugin_file ) {
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				continue;
			}

			$plugin_content = file_get_contents( WP_PLUGIN_DIR . '/' . $plugin_file );

			// Check for direct database table creation without dbDelta.
			if ( preg_match( '/CREATE\s+TABLE/i', $plugin_content ) ) {
				if ( ! preg_match( '/dbDelta/i', $plugin_content ) ) {
					$problematic_plugins[] = array(
						'plugin' => $plugin_file,
						'issue'  => 'Creates database tables without dbDelta()',
					);
				}
			}

			// Check for activation hooks that might timeout.
			if ( preg_match( '/register_activation_hook/i', $plugin_content ) ) {
				// Check for time-consuming operations.
				if ( preg_match( '/wp_remote_(get|post|request)|curl_exec|file_get_contents\s*\(\s*[\'"]http/i', $plugin_content ) ) {
					$problematic_plugins[] = array(
						'plugin' => $plugin_file,
						'issue'  => 'Activation hook may make external requests',
					);
				}
			}
		}

		// Check for orphaned activation hooks (plugin deleted but hook remains).
		global $wpdb;
		$options_with_activation = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options}
			WHERE option_name LIKE '%_activation_%'
			OR option_name LIKE 'plugin_%_version'
			LIMIT 50",
			ARRAY_A
		);

		$orphaned_count = 0;
		foreach ( $options_with_activation as $option ) {
			// Extract plugin slug from option name.
			preg_match( '/([a-zA-Z0-9_-]+)/', $option['option_name'], $matches );
			if ( ! empty( $matches[1] ) ) {
				$slug = $matches[1];

				// Check if any plugin contains this slug.
				$found = false;
				foreach ( array_keys( $all_plugins ) as $plugin_file ) {
					if ( strpos( $plugin_file, $slug ) !== false ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$orphaned_count++;
				}
			}
		}

		if ( $orphaned_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned options */
				__( '%d orphaned plugin activation options found', 'wpshadow' ),
				$orphaned_count
			);
		}

		// Check for plugins that failed to activate.
		$recently_active = get_option( 'recently_activated', array() );
		if ( count( $recently_active ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of recently deactivated plugins */
				__( '%d plugins were recently auto-deactivated (may indicate activation failures)', 'wpshadow' ),
				count( $recently_active )
			);
		}

		if ( ! empty( $issues ) || ! empty( $problematic_plugins ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugins may have activation hook issues', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'     => array(
					'problematic_plugins' => array_slice( $problematic_plugins, 0, 10 ),
					'orphaned_count'      => $orphaned_count,
					'recently_active'     => count( $recently_active ),
					'issues'              => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-activation-hook-issues',
			);
		}

		return null;
	}
}
