<?php
/**
 * Log File Size Diagnostic
 *
 * Checks if log files are getting too large.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log File Size Diagnostic Class
 *
 * Verifies that debug and error log files are not growing
 * excessively, which can consume server disk space.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Log_File_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'log-file-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Log File Size';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Debug/error logs not exceeding safe size limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the log file size check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if log files too large, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();
		$log_files = array();

		// Check WordPress debug log.
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log_path ) && is_readable( $debug_log_path ) ) {
			$debug_size = filesize( $debug_log_path );
			$debug_size_mb = $debug_size / ( 1024 * 1024 );

			$log_files['debug.log'] = array(
				'size_bytes' => $debug_size,
				'size_mb'    => round( $debug_size_mb, 2 ),
				'path'       => $debug_log_path,
			);

			$stats['debug_log_size_mb'] = round( $debug_size_mb, 2 );

			// Check if debug log is too large (>100MB).
			if ( $debug_size > 100 * 1024 * 1024 ) {
				$issues[] = sprintf(
					/* translators: %s: size */
					__( 'Debug log is %sMB - may impact performance', 'wpshadow' ),
					round( $debug_size_mb, 1 )
				);
			} elseif ( $debug_size > 50 * 1024 * 1024 ) {
				$issues[] = sprintf(
					/* translators: %s: size */
					__( 'Debug log is %sMB - consider archiving', 'wpshadow' ),
					round( $debug_size_mb, 1 )
				);
			}

			// Check if debug logging is enabled.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$stats['wp_debug_enabled'] = true;

				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					$stats['wp_debug_log_enabled'] = true;
				}
			}
		}

		// Check for plugin-specific logs.
		$plugin_log_dir = WP_CONTENT_DIR . '/plugins/wordfence/';
		if ( is_dir( $plugin_log_dir ) && is_readable( $plugin_log_dir ) ) {
			$wordfence_logs = glob( $plugin_log_dir . '*.log' );
			if ( ! empty( $wordfence_logs ) ) {
				foreach ( $wordfence_logs as $log_file ) {
					$log_size = filesize( $log_file );
					$log_size_mb = $log_size / ( 1024 * 1024 );

					$log_files[ basename( $log_file ) ] = array(
						'size_bytes' => $log_size,
						'size_mb'    => round( $log_size_mb, 2 ),
						'path'       => $log_file,
					);

					if ( $log_size > 100 * 1024 * 1024 ) {
						$issues[] = sprintf(
							/* translators: %s: filename, %s: size */
							__( '%s is %sMB - large plugin log file', 'wpshadow' ),
							basename( $log_file ),
							round( $log_size_mb, 1 )
						);
					}
				}
			}
		}

		$stats['log_files'] = $log_files;
		$stats['total_log_size_mb'] = round( array_sum( array_map( fn( $f ) => $f['size_bytes'], $log_files ) ) / ( 1024 * 1024 ), 2 );

		// Check if total logs exceed 500MB.
		if ( $stats['total_log_size_mb'] > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: size */
				__( 'Total log files are %sMB - implement log rotation', 'wpshadow' ),
				$stats['total_log_size_mb']
			);
		}

		// Check for log rotation configuration.
		$log_rotation = get_option( 'wpshadow_log_rotation_enabled' );
		$stats['log_rotation_enabled'] = boolval( $log_rotation );

		if ( ! $log_rotation && ! empty( $log_files ) ) {
			$issues[] = __( 'Log rotation not configured - logs will grow indefinitely', 'wpshadow' );
		}

		// If issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Log file size issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/log-management',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // Log files within acceptable size.
	}
}
