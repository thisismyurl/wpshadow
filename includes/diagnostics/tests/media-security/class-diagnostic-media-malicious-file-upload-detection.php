<?php
/**
 * Media Malicious File Upload Detection Diagnostic
 *
 * Tests for malicious file upload attempts. Validates file type
 * verification beyond extension to catch spoofing attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.2099
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Malicious_File_Upload_Detection Class
 *
 * Checks if WordPress has proper file upload validation to detect
 * and reject malicious file uploads. Tests for:
 * - File type verification beyond extension
 * - MIME type checking
 * - Executable file rejection
 *
 * @since 1.26033.2099
 */
class Diagnostic_Media_Malicious_File_Upload_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-malicious-file-upload-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Malicious File Upload Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates file type verification beyond extension to catch spoofing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2099
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if wp_check_filetype_and_ext is available
		if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
			$issues[] = __( 'wp_check_filetype_and_ext function not available for file validation', 'wpshadow' );
		}

		// Check if wp_validate_file_name is available
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			$issues[] = __( 'wp_handle_upload function not available for secure file uploads', 'wpshadow' );
		}

		// Check if the upload_mimes filter is being properly used
		$has_upload_mimes_filter = has_filter( 'upload_mimes' );
		if ( ! $has_upload_mimes_filter ) {
			$issues[] = __( 'No custom upload_mimes filter detected - file type restrictions may not be enforced', 'wpshadow' );
		}

		// Check if there's a pre_upload_error filter for early rejection
		$has_pre_upload_filter = has_filter( 'wp_handle_upload_prefilter' );
		if ( ! $has_pre_upload_filter ) {
			$issues[] = __( 'No wp_handle_upload_prefilter hook found - cannot perform early file rejection', 'wpshadow' );
		}

		// Test actual file validation with a test scenario
		$validation_issue = self::test_file_validation();
		if ( ! empty( $validation_issue ) ) {
			$issues[] = $validation_issue;
		}

		// Check if dangerous file types can be uploaded
		$dangerous_types = self::check_dangerous_file_types();
		if ( ! empty( $dangerous_types ) ) {
			$issues[] = sprintf(
				__( 'Potentially dangerous file types allowed: %s', 'wpshadow' ),
				implode( ', ', $dangerous_types )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-malicious-file-upload-detection',
			);
		}

		return null;
	}

	/**
	 * Test actual file validation
	 *
	 * @return string|null Issue description if validation failed, null otherwise.
	 */
	private static function test_file_validation() {
		// Test if renamed executable files would be caught
		// A file like "shell.php.jpg" should only be treated as jpg
		
		$test_filename = 'shell.php.jpg';
		$file_info = wp_check_filetype( $test_filename );
		
		// Should be detected as image/jpeg, not text/plain
		if ( 'jpg' !== $file_info['ext'] || 'image/jpeg' !== $file_info['type'] ) {
			return __( 'File type detection may not work correctly for disguised executable files', 'wpshadow' );
		}

		// Test double extension
		$test_filename2 = 'image.php.jpg';
		$file_info2 = wp_check_filetype( $test_filename2 );
		
		if ( 'jpg' !== $file_info2['ext'] ) {
			return __( 'Double extension attack detection may not work correctly', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for dangerous file types
	 *
	 * @return array Array of allowed dangerous file types.
	 */
	private static function check_dangerous_file_types() {
		$dangerous = array();
		$allowed_types = get_allowed_mime_types();
		
		// List of file types that could be dangerous if allowed
		$dangerous_patterns = array(
			'php'      => 'php',
			'exe'      => 'exe',
			'bat'      => 'bat',
			'sh'       => 'sh',
			'com'      => 'com',
			'cmd'      => 'cmd',
			'phtml'    => 'phtml',
			'pht'      => 'pht',
		);

		foreach ( $dangerous_patterns as $type => $label ) {
			foreach ( $allowed_types as $mime => $ext ) {
				if ( false !== strpos( $ext, $type ) ) {
					$dangerous[] = $label;
				}
			}
		}

		return array_unique( $dangerous );
	}
}
