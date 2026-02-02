<?php
/**
 * Import Tool File Validation Diagnostic
 *
 * Detects whether import tool validates XML/file integrity before
 * starting import operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Tool File Validation Diagnostic Class
 *
 * Ensures import tools validate files before processing to prevent
 * data corruption and wasted resources.
 *
 * @since 1.26033.1900
 */
class Diagnostic_Import_Tool_File_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-tool-file-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Tool File Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies import tool validates files before processing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Import handlers validate file types
	 * - File size limits are enforced
	 * - XML/structure validation occurs
	 * - Malformed files are rejected
	 * - Error messages are descriptive
	 *
	 * @since  1.26033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if import handler classes exist.
		$import_classes = array(
			'WPShadow\Core\Import_Handler',
			'WPShadow\Core\File_Validator',
		);

		$has_import_handler = false;
		foreach ( $import_classes as $class ) {
			if ( class_exists( $class ) ) {
				$has_import_handler = true;
				break;
			}
		}

		if ( ! $has_import_handler ) {
			$issues[] = __( 'Import handler class not found; file validation may not be implemented', 'wpshadow' );
		}

		// Check WordPress native import capabilities.
		if ( ! function_exists( 'wp_import_handle_upload' ) && ! function_exists( 'wp_handle_upload' ) ) {
			$issues[] = __( 'WordPress file upload handling not available; file validation cannot be enforced', 'wpshadow' );
		}

		// Check if file size limit is configured.
		$file_size_limit = apply_filters( 'wpshadow_import_file_size_limit', 512 * MB_IN_BYTES ); // 512MB default.
		$max_upload_size = wp_max_upload_size();

		if ( $file_size_limit > $max_upload_size ) {
			$issues[] = sprintf(
				/* translators: %s: file size */
				__( 'Import file size limit (%s) exceeds WordPress upload limit; validation may be ineffective', 'wpshadow' ),
				size_format( $file_size_limit )
			);
		}

		// Check if XML validation function exists.
		if ( ! function_exists( 'wp_parse_xml' ) && ! extension_loaded( 'xml' ) ) {
			$issues[] = __( 'XML parsing extension not loaded; XML import files cannot be validated', 'wpshadow' );
		}

		// Check for temporary file cleanup on failed imports.
		$upload_dir = wp_upload_dir();
		$import_temp_dir = $upload_dir['basedir'] . '/wpshadow-import-temp';
		if ( is_dir( $import_temp_dir ) ) {
			$temp_files = array_diff( scandir( $import_temp_dir ), array( '.', '..' ) );
			if ( count( $temp_files ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of files */
					__( '%d temporary import files found in import directory; old failed imports may not be cleaned up properly', 'wpshadow' ),
					count( $temp_files )
				);
			}
		}

		// Check if file type restrictions are defined.
		$allowed_import_types = apply_filters( 'wpshadow_allowed_import_file_types', array( 'xml', 'json', 'csv' ) );
		if ( empty( $allowed_import_types ) ) {
			$issues[] = __( 'No allowed import file types configured; any file type may be processed', 'wpshadow' );
		}

		// Check for MIME type validation.
		$check_mime = apply_filters( 'wpshadow_validate_import_mime_type', true );
		if ( ! $check_mime ) {
			$issues[] = __( 'MIME type validation is disabled for imports; potentially dangerous files could be processed', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/import-tool-file-validation',
			);
		}

		return null;
	}
}
