<?php
/**
 * Plugin Database Bloat Diagnostic
 *
 * Detects plugins accumulating excessive database records.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Database Bloat Class
 *
 * Identifies plugins creating excessive database entries.
 * Common culprits: logging, analytics, revision history plugins.
 *
 * @since 1.5029.1630
 */
class Diagnostic_Plugin_Database_Bloat extends Diagnostic_Base {

	protected static $slug        = 'plugin-database-bloat';
	protected static $title       = 'Plugin Database Bloat';
	protected static $description = 'Detects plugins accumulating excessive database records';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_plugin_database_bloat';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Get all option keys grouped by plugin prefix.
		$options = $wpdb->get_results(
			"SELECT option_name FROM {$wpdb->options} WHERE autoload = 'yes' ORDER BY option_name",
			ARRAY_A
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$plugin_option_counts = array();

		foreach ( $options as $option ) {
			$option_name = $option['option_name'];
			
			// Extract plugin prefix (first part before underscore).
			if ( preg_match( '/^([a-zA-Z0-9]+)_/', $option_name, $matches ) ) {
				$prefix = $matches[1];
				
				if ( ! isset( $plugin_option_counts[ $prefix ] ) ) {
					$plugin_option_counts[ $prefix ] = 0;
				}
				$plugin_option_counts[ $prefix ]++;
			}
		}

		// Sort by count descending.
		arsort( $plugin_option_counts );

		$bloated_plugins = array();

		foreach ( $plugin_option_counts as $prefix => $count ) {
			// Threshold: 100+ autoloaded options from same plugin.
			if ( $count > 100 ) {
				// Estimate data size.
				$size_query = $wpdb->prepare(
					"SELECT SUM(LENGTH(option_value)) as total_size 
					FROM {$wpdb->options} 
					WHERE option_name LIKE %s AND autoload = 'yes'",
					$prefix . '_%'
				);
				
				$size_result = $wpdb->get_var( $size_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				$size_kb = round( $size_result / 1024, 2 );

				$bloated_plugins[] = array(
					'prefix'       => $prefix,
					'option_count' => $count,
					'size_kb'      => $size_kb,
				);
			}
		}

		if ( ! empty( $bloated_plugins ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of bloated plugins */
					__( '%d plugins are bloating the database with excessive autoloaded options. Performance impact.', 'wpshadow' ),
					count( $bloated_plugins )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-plugin-database-bloat',
				'data'         => array(
					'bloated_plugins' => $bloated_plugins,
					'total_checked'   => count( $plugin_option_counts ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
