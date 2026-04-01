<?php
/**
 * Plugin Uninstall Cleanup Check Diagnostic
 *
 * Verifies that plugins properly clean up data during uninstallation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Uninstall Cleanup Check Diagnostic Class
 *
 * Checks for proper uninstall.php implementation and leftover data.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Uninstall_Cleanup_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-uninstall-cleanup-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Uninstall Cleanup Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper plugin cleanup on uninstall';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$plugins_without_uninstall = array();

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		// Check each plugin for uninstall.php.
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );
			$uninstall_file = $plugin_dir . '/uninstall.php';

			if ( ! file_exists( $uninstall_file ) ) {
				// Check if plugin registers uninstall hook in main file.
				$main_file = WP_PLUGIN_DIR . '/' . $plugin_file;
				if ( file_exists( $main_file ) ) {
					$content = file_get_contents( $main_file );
					if ( ! preg_match( '/register_uninstall_hook/i', $content ) ) {
						$plugins_without_uninstall[] = $plugin_data['Name'];
					}
				}
			}
		}

		if ( count( $plugins_without_uninstall ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				_n(
					'%d plugin lacks uninstall cleanup',
					'%d plugins lack uninstall cleanup',
					count( $plugins_without_uninstall ),
					'wpshadow'
				),
				count( $plugins_without_uninstall )
			);
		}

		// Check for orphaned plugin options (from deleted plugins).
		$option_prefixes = array();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$slug = sanitize_key( dirname( $plugin_file ) );
			$option_prefixes[] = $slug;
		}

		// Get all options.
		$all_options = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name NOT LIKE '\_%'" );

		$orphaned_options = array();
		foreach ( $all_options as $option_name ) {
			// Skip WordPress core options.
			if ( in_array( $option_name, array( 'siteurl', 'home', 'blogname', 'blogdescription' ), true ) ) {
				continue;
			}

			// Check if option matches any active plugin.
			$is_orphaned = true;
			foreach ( $option_prefixes as $prefix ) {
				if ( strpos( $option_name, $prefix ) === 0 ) {
					$is_orphaned = false;
					break;
				}
			}

			if ( $is_orphaned && preg_match( '/^[a-z_-]+_/', $option_name ) ) {
				$orphaned_options[] = $option_name;
			}

			if ( count( $orphaned_options ) > 50 ) {
				break; // Limit check.
			}
		}

		if ( count( $orphaned_options ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned options */
				__( '%d orphaned plugin options detected (from deleted plugins)', 'wpshadow' ),
				count( $orphaned_options )
			);
		}

		// Check for orphaned plugin tables.
		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$orphaned_tables = array();

		foreach ( $tables as $table ) {
			// Skip core WordPress tables.
			$core_tables = array( 'posts', 'postmeta', 'users', 'usermeta', 'options', 'links', 'comments', 'commentmeta', 'terms', 'term_relationships', 'term_taxonomy', 'termmeta' );

			$table_name = str_replace( $wpdb->prefix, '', $table );
			if ( in_array( $table_name, $core_tables, true ) ) {
				continue;
			}

			// Check if table matches any active plugin.
			$is_orphaned = true;
			foreach ( $option_prefixes as $prefix ) {
				if ( strpos( $table_name, $prefix ) === 0 ) {
					$is_orphaned = false;
					break;
				}
			}

			if ( $is_orphaned ) {
				$orphaned_tables[] = $table_name;
			}
		}

		if ( count( $orphaned_tables ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned tables */
				__( '%d orphaned database tables found (from deleted plugins)', 'wpshadow' ),
				count( $orphaned_tables )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugins may not properly clean up data on uninstall', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'details'     => array(
					'plugins_without_uninstall' => array_slice( $plugins_without_uninstall, 0, 10 ),
					'orphaned_options_count'    => count( $orphaned_options ),
					'orphaned_tables'           => array_slice( $orphaned_tables, 0, 10 ),
					'issues'                    => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-uninstall-cleanup-check?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
