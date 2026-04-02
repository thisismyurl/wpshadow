<?php
/**
 * Plugin Activity Logging Diagnostic
 *
 * Checks if plugins are logging activity or creating excessive log entries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Activity Logging Diagnostic Class
 *
 * Detects excessive logging or debug output from plugins.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Activity_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-activity-logging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Activity Logging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive plugin logging';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$large_logs = array();

		// Common plugin log locations.
		$log_locations = array(
			WP_CONTENT_DIR . '/debug.log',
			WP_CONTENT_DIR . '/uploads/wc-logs/',
			WP_CONTENT_DIR . '/cache/',
			WP_PLUGIN_DIR . '/*/logs/',
		);

		$total_log_size = 0;

		foreach ( $log_locations as $location ) {
			if ( strpos( $location, '*' ) !== false ) {
				// Glob pattern.
				$files = glob( $location . '*.log' );
			} else {
				// Single file or directory.
				if ( is_dir( $location ) ) {
					$files = glob( $location . '*.log' );
				} elseif ( file_exists( $location ) ) {
					$files = array( $location );
				} else {
					$files = array();
				}
			}

			foreach ( $files as $file ) {
				$size = filesize( $file );
				$total_log_size += $size;

				if ( $size > 10485760 ) { // > 10MB.
					$large_logs[] = array(
						'file' => basename( dirname( $file ) ) . '/' . basename( $file ),
						'size' => size_format( $size ),
					);
				}
			}
		}

		if ( $total_log_size > 52428800 ) { // > 50MB total.
			$issues[] = sprintf(
				/* translators: %s: total log size */
				__( 'Plugin logs consuming %s of disk space', 'wpshadow' ),
				size_format( $total_log_size )
			);
		}

		if ( ! empty( $large_logs ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of large log files */
				_n(
					'%d large log file found (>10MB)',
					'%d large log files found (>10MB)',
					count( $large_logs ),
					'wpshadow'
				),
				count( $large_logs )
			);
		}

		// Check for WP_DEBUG_LOG enabled.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$debug_log = WP_CONTENT_DIR . '/debug.log';
			if ( file_exists( $debug_log ) ) {
				$size = filesize( $debug_log );
				if ( $size > 5242880 ) { // > 5MB.
					$issues[] = sprintf(
						/* translators: %s: debug log size */
						__( 'WP_DEBUG_LOG enabled with %s debug.log', 'wpshadow' ),
						size_format( $size )
					);
				}
			}
		}

		// Check for plugins with debug mode enabled.
		$debug_options = array(
			'woocommerce_debug_mode'     => 'WooCommerce',
			'wordfence_debugOn'          => 'Wordfence',
			'itsec_log_rotation'         => 'iThemes Security',
		);

		$debug_plugins = array();
		foreach ( $debug_options as $option => $plugin_name ) {
			if ( get_option( $option ) ) {
				$debug_plugins[] = $plugin_name;
			}
		}

		if ( ! empty( $debug_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated plugin names */
				__( 'Debug mode enabled for: %s', 'wpshadow' ),
				implode( ', ', $debug_plugins )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugins generating excessive log files or debug output', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'details'     => array(
					'total_log_size' => size_format( $total_log_size ),
					'large_logs'     => array_slice( $large_logs, 0, 10 ),
					'debug_plugins'  => $debug_plugins,
					'issues'         => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-activity-logging',
			);
		}

		return null;
	}
}
