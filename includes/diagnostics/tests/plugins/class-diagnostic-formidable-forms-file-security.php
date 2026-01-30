<?php
/**
 * Formidable Forms File Security Diagnostic
 *
 * Formidable Forms file uploads insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.261.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms File Security Diagnostic Class
 *
 * @since 1.261.0000
 */
class Diagnostic_FormidableFormsFileSecurity extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-file-security';
	protected static $title = 'Formidable Forms File Security';
	protected static $description = 'Formidable Forms file uploads insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: File upload directory permissions
		$upload_dir = get_option( 'frm_upload_dir', WP_CONTENT_DIR . '/uploads/formidable/' );
		if ( is_dir( $upload_dir ) ) {
			$perms = fileperms( $upload_dir );
			if ( ( $perms & 0004 ) > 0 ) {
				$issues[] = 'upload directory world-readable (security risk)';
			}
		}
		
		// Check 2: Allowed file types
		$allowed_types = get_option( 'frm_allowed_file_types', '' );
		if ( empty( $allowed_types ) ) {
			$issues[] = 'no file type restrictions (any file can be uploaded)';
		} elseif ( strpos( $allowed_types, 'php' ) !== false || strpos( $allowed_types, 'exe' ) !== false ) {
			$issues[] = 'dangerous file types allowed (php, exe)';
		}
		
		// Check 3: File size limits
		$max_size = get_option( 'frm_max_file_size', 0 );
		if ( empty( $max_size ) || $max_size > 10485760 ) {
			$size_mb = ! empty( $max_size ) ? round( $max_size / 1048576 ) : 'unlimited';
			$issues[] = "large file upload limit ({$size_mb}MB, consider reducing)";
		}
		
		// Check 4: Direct file access protection
		if ( is_dir( $upload_dir ) ) {
			$htaccess = $upload_dir . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				$issues[] = 'no .htaccess protection (files directly accessible)';
			}
		}
		
		// Check 5: File name sanitization
		$sanitize_names = get_option( 'frm_sanitize_file_names', '1' );
		if ( '0' === $sanitize_names ) {
			$issues[] = 'file name sanitization disabled (XSS risk)';
		}
		
		// Check 6: Virus scanning integration
		$virus_scan = get_option( 'frm_virus_scan_enabled', '0' );
		if ( '0' === $virus_scan && ! empty( $allowed_types ) ) {
			$issues[] = 'no virus scanning for uploads';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Formidable Forms file security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-file-security',
			);
		}
		
		return null;
	}
}
