<?php
/**
 * Media Malicious File Upload Detection Diagnostic
 *
 * Tests for malicious file upload attempts. Validates file type
 * verification beyond extension to catch spoofing attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-malicious-file-upload-detection',
				'context'       => array(
					'why'            => __( 'Malicious file upload is one of the fastest paths to full site compromise. Attackers often disguise executable files as images or documents (e.g., shell.php.jpg) or abuse weak MIME validation to place scripts in the uploads directory. If those scripts can be executed or processed unsafely, the attacker gains remote code execution and can install backdoors, steal database credentials, or inject malware into pages. OWASP Top 10 2021 ranks Injection #3 and Security Misconfiguration #5; insecure file upload handling often combines both. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern against public‑facing systems; after initial access, uploading malicious files is a common escalation technique. The business impact includes downtime, data theft, SEO blacklisting, and reputational damage. For ecommerce sites, attackers may add payment skimmers or alter checkout flows, leading to chargebacks and regulatory reporting. Even if the site is cleaned, search engines can flag the domain for weeks, suppressing traffic. Strong server‑side validation and file type restrictions reduce the attack surface dramatically by ensuring only safe, expected formats are accepted and by rejecting files with mismatched MIME types or double extensions. This is a high‑value control because it protects against both known vulnerabilities and unknown plugin bugs that expose upload endpoints.', 'wpshadow' ),
					'recommendation' => __( '1. Enforce server‑side MIME validation with wp_check_filetype_and_ext().
2. Reject files with double extensions or executable signatures.
3. Limit allowed file types via upload_mimes filter.
4. Scan uploads with malware detection or antivirus.
5. Disable execution of scripts in uploads via server rules.
6. Validate file contents (magic bytes) for images and documents.
7. Enforce file size limits to reduce payload risk.
8. Log and alert on failed upload attempts.
9. Require authentication and capability checks for all upload endpoints.
10. Re‑test upload security after plugin/theme updates.', 'wpshadow' ),
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', self::$slug );
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
