<?php
/**
 * Diagnostic: Error Log Rotation Status
 *
 * Checks if error logs are growing excessively and need rotation.
 * Large error logs can consume disk space and impact performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Maintenance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Error_Log_Rotation
 *
 * Monitors error log file sizes and recommends rotation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Error_Log_Rotation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'error-log-rotation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Error Log Rotation Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error logs need rotation';

	/**
	 * File size threshold in bytes (10 MB).
	 */
	const SIZE_THRESHOLD = 10485760;

	/**
	 * Check error log rotation status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$large_logs = array();

		// Check common error log locations.
		$log_paths = array(
			WP_CONTENT_DIR . '/debug.log',
			ini_get( 'error_log' ),
			ABSPATH . 'error_log',
			ABSPATH . 'error.log',
		);

		foreach ( $log_paths as $log_path ) {
			if ( empty( $log_path ) || ! file_exists( $log_path ) ) {
				continue;
			}

			// Get file size.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$size = @filesize( $log_path );

			if ( $size === false ) {
				continue;
			}

			// Warn if log is larger than threshold.
			if ( $size > self::SIZE_THRESHOLD ) {
				$large_logs[ $log_path ] = array(
					'size'       => $size,
					'size_human' => size_format( $size ),
				);
			}
		}

		if ( ! empty( $large_logs ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of large error logs */
					_n(
						'%d error log file is excessively large and should be rotated',
						'%d error log files are excessively large and should be rotated',
						count( $large_logs ),
						'wpshadow'
					),
					count( $large_logs )
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/error_log_rotation',
				'meta'        => array(
					'large_logs' => $large_logs,
					'threshold'  => self::SIZE_THRESHOLD,
				),
			);
		}

		// Error logs are at acceptable sizes.
		return null;
	}
}
