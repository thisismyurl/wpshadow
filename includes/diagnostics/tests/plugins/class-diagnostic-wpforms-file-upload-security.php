<?php
/**
 * WPForms File Upload Security Diagnostic
 *
 * WPForms file uploads not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.251.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms File Upload Security Diagnostic Class
 *
 * @since 1.251.0000
 */
class Diagnostic_WpformsFileUploadSecurity extends Diagnostic_Base {

	protected static $slug = 'wpforms-file-upload-security';
	protected static $title = 'WPForms File Upload Security';
	protected static $description = 'WPForms file uploads not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: File type validation
		$file_types = get_option( 'wpforms_file_upload_validation_enabled', 0 );
		if ( ! $file_types ) {
			$issues[] = 'File type validation not enabled';
		}

		// Check 2: File size limits
		$size_limit = absint( get_option( 'wpforms_file_upload_size_limit_mb', 0 ) );
		if ( $size_limit <= 0 ) {
			$issues[] = 'File size limit not configured';
		}

		// Check 3: Upload directory security
		$upload_sec = get_option( 'wpforms_upload_directory_secured', 0 );
		if ( ! $upload_sec ) {
			$issues[] = 'Upload directory not secured';
		}

		// Check 4: Filename sanitization
		$filename_san = get_option( 'wpforms_filename_sanitization_enabled', 0 );
		if ( ! $filename_san ) {
			$issues[] = 'Filename sanitization not enabled';
		}

		// Check 5: Anti-virus scanning
		$antivirus = get_option( 'wpforms_antivirus_scanning_enabled', 0 );
		if ( ! $antivirus ) {
			$issues[] = 'Antivirus scanning not enabled';
		}

		// Check 6: Upload confirmation
		$confirm = get_option( 'wpforms_upload_confirmation_enabled', 0 );
		if ( ! $confirm ) {
			$issues[] = 'Upload confirmation not required';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d file upload security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-file-upload-security',
			);
		}

		return null;
	}
}
