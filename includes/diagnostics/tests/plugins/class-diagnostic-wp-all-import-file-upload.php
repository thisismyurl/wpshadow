<?php
/**
 * WP All Import File Upload Security Diagnostic
 *
 * Import file uploads not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.272.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import File Upload Security Diagnostic Class
 *
 * @since 1.272.0000
 */
class Diagnostic_WpAllImportFileUpload extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-file-upload';
	protected static $title = 'WP All Import File Upload Security';
	protected static $description = 'Import file uploads not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: File type validation
		$allowed_types = get_option( 'pmxi_allowed_file_types', array( 'xml', 'csv', 'txt', 'zip' ) );
		if ( in_array( 'php', $allowed_types, true ) || in_array( 'exe', $allowed_types, true ) ) {
			$issues[] = __( 'Executable file types allowed (code injection risk)', 'wpshadow' );
		}
		
		// Check 2: File size limit
		$max_file_size = get_option( 'pmxi_max_file_size', 0 );
		if ( $max_file_size === 0 || $max_file_size > ( 100 * 1024 * 1024 ) ) {
			$issues[] = __( 'No file size limit (DoS risk)', 'wpshadow' );
		}
		
		// Check 3: Upload directory permissions
		$upload_dir = wp_upload_dir();
		$import_dir = $upload_dir['basedir'] . '/wpallimport/files/';
		
		if ( is_dir( $import_dir ) ) {
			$perms = fileperms( $import_dir );
			if ( ( $perms & 0002 ) ) {
				$issues[] = __( 'Import directory world-writable (security risk)', 'wpshadow' );
			}
		}
		
		// Check 4: Malware scanning
		$scan_uploads = get_option( 'pmxi_scan_uploads', 'no' );
		if ( 'no' === $scan_uploads ) {
			$issues[] = __( 'No malware scanning (infected files possible)', 'wpshadow' );
		}
		
		// Check 5: User capability check
		$required_capability = get_option( 'pmxi_required_capability', 'manage_options' );
		if ( 'edit_posts' === $required_capability ) {
			$issues[] = __( 'Low capability requirement (unauthorized imports)', 'wpshadow' );
		}
		
		// Check 6: File retention
		$auto_delete = get_option( 'pmxi_auto_delete_files', 'no' );
		if ( 'no' === $auto_delete ) {
			$issues[] = __( 'Files not auto-deleted (disk space waste)', 'wpshadow' );
		}
		
		// Check 7: URL import validation
		$validate_urls = get_option( 'pmxi_validate_import_urls', 'no' );
		if ( 'no' === $validate_urls ) {
			$issues[] = __( 'URL validation disabled (SSRF risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 90;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 83;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of file upload security issues */
				__( 'WP All Import has %d file upload security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-file-upload',
		);
	}
}
