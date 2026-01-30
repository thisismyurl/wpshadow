<?php
/**
 * Ninja Forms File Upload Security Diagnostic
 *
 * Ninja Forms File Upload Security issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1189.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms File Upload Security Diagnostic Class
 *
 * @since 1.1189.0000
 */
class Diagnostic_NinjaFormsFileUploadSecurity extends Diagnostic_Base {

	protected static $slug = 'ninja-forms-file-upload-security';
	protected static $title = 'Ninja Forms File Upload Security';
	protected static $description = 'Ninja Forms File Upload Security issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify file type restrictions
		$allowed_types = get_option( 'ninja_forms_file_upload_types', array() );
		if ( empty( $allowed_types ) || in_array( 'exe', $allowed_types, true ) || in_array( 'php', $allowed_types, true ) ) {
			$issues[] = 'Dangerous file types not properly restricted';
		}
		
		// Check 2: Check file size limits
		$max_size = get_option( 'ninja_forms_max_file_size', 0 );
		if ( $max_size <= 0 || $max_size > 10485760 ) {
			$issues[] = 'File size limit not properly configured (max 10MB recommended)';
		}
		
		// Check 3: Verify upload directory permissions
		$upload_dir = wp_upload_dir();
		$nf_upload_dir = $upload_dir['basedir'] . '/ninja-forms';
		if ( file_exists( $nf_upload_dir ) ) {
			$perms = fileperms( $nf_upload_dir );
			if ( ( $perms & 0x0002 ) || ( $perms & 0x0080 ) ) {
				$issues[] = 'Upload directory has world-writable permissions';
			}
		}
		
		// Check 4: Check for antivirus scanning
		$antivirus = get_option( 'ninja_forms_antivirus_scan', 0 );
		if ( ! $antivirus ) {
			$issues[] = 'Antivirus scanning not enabled for uploads';
		}
		
		// Check 5: Verify filename sanitization
		$sanitize_filenames = get_option( 'ninja_forms_sanitize_filenames', 0 );
		if ( ! $sanitize_filenames ) {
			$issues[] = 'Filename sanitization not enabled';
		}
		
		// Check 6: Check for upload access restrictions
		$restrict_access = get_option( 'ninja_forms_restrict_upload_access', 0 );
		if ( ! $restrict_access ) {
			$issues[] = 'Upload file access restrictions not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Ninja Forms file upload security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-forms-file-upload-security',
			);
		}
		
		return null;
	}
}
