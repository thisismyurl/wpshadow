<?php
/**
 * Import Log File Issues Treatment
 *
 * Tests whether import logs are created and accessible.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Log File Issues Treatment Class
 *
 * Tests whether import logs are created and accessible for troubleshooting.
 *
 * @since 1.6033.0000
 */
class Treatment_Import_Log_File_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-log-file-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Log File Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether import logs are created and accessible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for debug logging enabled.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			$issues[] = __( 'WP_DEBUG_LOG is not enabled - import errors not logged', 'wpshadow' );
		}

		// Check for debug.log file.
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( ! file_exists( $debug_log_path ) ) {
			$issues[] = __( 'debug.log file does not exist', 'wpshadow' );
		} else {
			// Check if file is writable.
			if ( ! is_writable( $debug_log_path ) ) {
				$issues[] = __( 'debug.log file is not writable', 'wpshadow' );
			}

			// Check file size (warn if too large).
			$file_size = filesize( $debug_log_path );
			if ( $file_size > 10485760 ) { // > 10MB
				$issues[] = sprintf(
					/* translators: %s: file size */
					__( 'debug.log file is very large (%s) - may impact performance', 'wpshadow' ),
					size_format( $file_size )
				);
			}
		}

		// Check for import-specific logs.
		$import_log_path = WP_CONTENT_DIR . '/wpshadow-import.log';
		if ( ! file_exists( $import_log_path ) ) {
			$issues[] = __( 'No import-specific log file found', 'wpshadow' );
		} else {
			if ( ! is_writable( dirname( $import_log_path ) ) ) {
				$issues[] = __( 'Import log directory is not writable', 'wpshadow' );
			}
		}

		// Check for logging directory permissions.
		$log_dir = WP_CONTENT_DIR . '/logs';
		if ( ! is_dir( $log_dir ) ) {
			if ( ! is_writable( WP_CONTENT_DIR ) ) {
				$issues[] = __( 'Cannot create logs directory - content directory not writable', 'wpshadow' );
			}
		} else {
			if ( ! is_writable( $log_dir ) ) {
				$issues[] = __( 'Logs directory exists but is not writable', 'wpshadow' );
			}
		}

		// Check for error handling in imports.
		if ( ! has_action( 'import_error' ) ) {
			$issues[] = __( 'No import error handling hook detected', 'wpshadow' );
		}

		// Check for log rotation (to prevent unlimited growth).
		if ( ! has_action( 'wpshadow_rotate_import_log' ) ) {
			$issues[] = __( 'No log rotation hook registered', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-log-file-issues',
			);
		}

		return null;
	}
}
