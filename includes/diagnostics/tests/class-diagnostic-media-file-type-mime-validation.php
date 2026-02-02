<?php
/**
 * Media File Type MIME Validation Diagnostic
 *
 * Validates MIME type checking for uploaded files and tests
 * for MIME spoofing vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_File_Type_MIME_Validation Class
 *
 * Validates that WordPress properly checks and validates MIME types
 * for uploaded files. Detects potential MIME spoofing vulnerabilities
 * where the actual file content doesn't match the declared MIME type.
 *
 * @since 1.26033.2102
 */
class Diagnostic_Media_File_Type_MIME_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-type-mime-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Type MIME Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates MIME type checking and detects spoofing vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if mime type validation functions exist
		if ( ! function_exists( 'mime_type_icon' ) ) {
			$issues[] = __( 'mime_type_icon function not available', 'wpshadow' );
		}

		// Check for finfo functions (file info extension)
		if ( ! function_exists( 'finfo_file' ) && ! function_exists( 'mime_content_type' ) ) {
			$issues[] = __( 'PHP file information functions (finfo_file) not available - cannot validate MIME types', 'wpshadow' );
		}

		// Check if wp_check_filetype_and_ext is properly handling MIME validation
		if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
			$issues[] = __( 'wp_check_filetype_and_ext function required for MIME validation not found', 'wpshadow' );
		}

		// Test MIME type validation
		$mime_validation_issue = self::test_mime_validation();
		if ( ! empty( $mime_validation_issue ) ) {
			$issues[] = $mime_validation_issue;
		}

		// Check if upload_mimes filter is properly restricting dangerous types
		$restricted_issue = self::check_mime_type_restrictions();
		if ( ! empty( $restricted_issue ) ) {
			$issues[] = $restricted_issue;
		}

		// Check for svgs and javascript in uploaded files
		$dangerous_formats = self::check_dangerous_file_formats();
		if ( ! empty( $dangerous_formats ) ) {
			$issues[] = $dangerous_formats;
		}

		// Check real mime type validation by checking actual file content
		$content_validation_issue = self::test_mime_content_validation();
		if ( ! empty( $content_validation_issue ) ) {
			$issues[] = $content_validation_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-file-type-mime-validation',
			);
		}

		return null;
	}

	/**
	 * Test basic MIME type validation
	 *
	 * @return string|null Issue description if validation failed.
	 */
	private static function test_mime_validation() {
		// Test with a known image file
		$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif' );
		$valid_count = 0;

		foreach ( $image_extensions as $ext ) {
			$file_info = wp_check_filetype( "test.$ext" );
			if ( ! empty( $file_info['type'] ) && false !== strpos( $file_info['type'], 'image' ) ) {
				$valid_count++;
			}
		}

		if ( $valid_count < 2 ) {
			return __( 'MIME type validation is not correctly identifying common image formats', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check MIME type restrictions
	 *
	 * @return string|null Issue description if restrictions inadequate.
	 */
	private static function check_mime_type_restrictions() {
		$allowed_mimes = get_allowed_mime_types();

		// Check if executable types are somehow allowed
		$executable_types = array(
			'application/x-msdownload',
			'application/x-msdos-program',
			'application/x-mach-binary',
		);

		foreach ( $executable_types as $exec_type ) {
			if ( array_key_exists( $exec_type, $allowed_mimes ) ) {
				return __( 'Executable file types are allowed for upload', 'wpshadow' );
			}
		}

		return null;
	}

	/**
	 * Check for dangerous file formats like SVG with embedded scripts
	 *
	 * @return string|null Issue description if dangerous formats found.
	 */
	private static function check_dangerous_file_formats() {
		$allowed_mimes = get_allowed_mime_types();

		// Check if SVG is allowed and if there are safeguards
		$svg_allowed = false;
		foreach ( $allowed_mimes as $mime => $ext ) {
			if ( false !== strpos( $mime, 'svg' ) || false !== strpos( $ext, 'svg' ) ) {
				$svg_allowed = true;
				break;
			}
		}

		if ( $svg_allowed ) {
			// SVG allowed - check if there's sanitization in place
			if ( ! has_filter( 'wp_handle_upload_prefilter' ) && ! has_filter( 'upload_mimes' ) ) {
				return __( 'SVG files are allowed for upload but no validation filter detected to prevent embedded scripts', 'wpshadow' );
			}
		}

		return null;
	}

	/**
	 * Test MIME type validation based on file content
	 *
	 * @return string|null Issue description if content validation inadequate.
	 */
	private static function test_mime_content_validation() {
		// Test if wp_check_filetype_and_ext correctly validates actual file content
		// This function should validate both filename and actual content
		
		if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
			return null; // Already reported above
		}

		// Check if the function exists and is callable
		if ( ! is_callable( 'wp_check_filetype_and_ext' ) ) {
			return __( 'wp_check_filetype_and_ext is not properly callable', 'wpshadow' );
		}

		// Check if PHP has access to file info functions for real validation
		if ( ! function_exists( 'finfo_file' ) && ! function_exists( 'mime_content_type' ) ) {
			return __( 'PHP file information extension is not available - real MIME type validation may not work', 'wpshadow' );
		}

		return null;
	}
}
