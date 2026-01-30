<?php
/**
 * Diagnostic: Plugin Deactivation Issues
 *
 * Detects deactivated plugins that are causing errors or leaving behind broken data.
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
 * Class Diagnostic_Plugin_Deactivation_Issues
 *
 * Identifies deactivated plugins that are still causing errors or leaving
 * orphaned data in the database, which can impact performance and stability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Plugin_Deactivation_Issues extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-deactivation-issues';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Deactivation Issues';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect deactivated plugins causing errors or leaving broken data';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for deactivated plugins that are still referenced in error logs
	 * or have orphaned data in the database.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		
		// Get inactive plugins
		$inactive_plugins = array();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( ! in_array( $plugin_file, $active_plugins, true ) ) {
				$inactive_plugins[ $plugin_file ] = $plugin_data;
			}
		}

		if ( empty( $inactive_plugins ) ) {
			return null;
		}

		$problematic_plugins = array();

		// Check for orphaned options in database
		global $wpdb;
		foreach ( $inactive_plugins as $plugin_file => $plugin_data ) {
			// Extract plugin slug from file path
			$plugin_slug = dirname( $plugin_file );
			if ( '.' === $plugin_slug ) {
				$plugin_slug = basename( $plugin_file, '.php' );
			}

			// Check for orphaned options (common pattern: plugin_slug_*)
			$option_pattern = $plugin_slug . '_%';
			$orphaned_options = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
					$option_pattern
				)
			);

			// Check for orphaned post meta
			$meta_pattern = '_' . $plugin_slug . '_%';
			$orphaned_postmeta = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
					$meta_pattern
				)
			);

			// Check for orphaned user meta
			$orphaned_usermeta = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
					$meta_pattern
				)
			);

			$total_orphaned = absint( $orphaned_options ) + absint( $orphaned_postmeta ) + absint( $orphaned_usermeta );

			// If significant orphaned data found (>10 rows), flag as problematic
			if ( $total_orphaned > 10 ) {
				$problematic_plugins[] = array(
					'name' => $plugin_data['Name'],
					'file' => $plugin_file,
					'slug' => $plugin_slug,
					'orphaned_data' => $total_orphaned,
					'orphaned_options' => absint( $orphaned_options ),
					'orphaned_postmeta' => absint( $orphaned_postmeta ),
					'orphaned_usermeta' => absint( $orphaned_usermeta ),
				);
			}
		}

		// Check error log for references to inactive plugins (if debug.log exists)
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log ) && is_readable( $debug_log ) ) {
			$log_content = file_get_contents( $debug_log );
			
			if ( false !== $log_content ) {
				// Get recent errors (last 100KB)
				$recent_log = substr( $log_content, -102400 );
				
				foreach ( $inactive_plugins as $plugin_file => $plugin_data ) {
					$plugin_dir = dirname( $plugin_file );
					if ( '.' !== $plugin_dir && false !== strpos( $recent_log, $plugin_dir ) ) {
						// Check if already in problematic list
						$found = false;
						foreach ( $problematic_plugins as &$prob_plugin ) {
							if ( $prob_plugin['file'] === $plugin_file ) {
								$prob_plugin['error_log_refs'] = true;
								$found = true;
								break;
							}
						}
						
						if ( ! $found ) {
							$problematic_plugins[] = array(
								'name' => $plugin_data['Name'],
								'file' => $plugin_file,
								'slug' => dirname( $plugin_file ),
								'error_log_refs' => true,
								'orphaned_data' => 0,
							);
						}
					}
				}
			}
		}

		if ( empty( $problematic_plugins ) ) {
			return null;
		}

		$problem_count = count( $problematic_plugins );
		$plugin_names = array_column( $problematic_plugins, 'name' );
		
		$description = sprintf(
			/* translators: %d: number of problematic deactivated plugins */
			_n(
				'Found %d deactivated plugin with issues (orphaned database data or errors). This can slow down your site and cause unexpected problems.',
				'Found %d deactivated plugins with issues (orphaned database data or errors). These can slow down your site and cause unexpected problems.',
				$problem_count,
				'wpshadow'
			),
			$problem_count
		) . ' ' . sprintf(
			/* translators: %s: comma-separated list of plugin names */
			__( 'Affected plugins: %s', 'wpshadow' ),
			esc_html( implode( ', ', $plugin_names ) )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/plugins-plugin-deactivation-issues',
			'meta'        => array(
				'problematic_plugins' => $problematic_plugins,
				'problem_count' => $problem_count,
			),
		);
	}
}
