<?php
/**
 * wpForo Attachment Security Diagnostic
 *
 * wpForo attachments not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.533.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Attachment Security Diagnostic Class
 *
 * @since 1.533.0000
 */
class Diagnostic_WpforoAttachmentSecurity extends Diagnostic_Base {

	protected static $slug = 'wpforo-attachment-security';
	protected static $title = 'wpForo Attachment Security';
	protected static $description = 'wpForo attachments not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify file type restrictions
		$allowed_types = get_option( 'wpforo_attachments_allowed_types', array() );
		if ( empty( $allowed_types ) || in_array( 'exe', $allowed_types, true ) || in_array( 'php', $allowed_types, true ) ) {
			$issues[] = 'Dangerous file types not properly restricted';
		}
		
		// Check 2: Check file size limits
		$max_size = get_option( 'wpforo_attachments_max_size', 0 );
		if ( $max_size <= 0 || $max_size > 10485760 ) {
			$issues[] = 'File size limit not properly configured (max 10MB recommended)';
		}
		
		// Check 3: Verify attachment directory permissions
		$upload_dir = wp_upload_dir();
		$wpforo_dir = $upload_dir['basedir'] . '/wpforo/attachments';
		if ( file_exists( $wpforo_dir ) ) {
			$perms = fileperms( $wpforo_dir );
			if ( ( $perms & 0x0002 ) || ( $perms & 0x0080 ) ) {
				$issues[] = 'Attachment directory has world-writable permissions';
			}
		}
		
		// Check 4: Verify htaccess protection
		$htaccess_file = $wpforo_dir . '/.htaccess';
		if ( file_exists( $wpforo_dir ) && ! file_exists( $htaccess_file ) ) {
			$issues[] = '.htaccess file missing from attachment directory';
		}
		
		// Check 5: Check for antivirus integration
		$antivirus = get_option( 'wpforo_attachments_antivirus', false );
		if ( ! $antivirus ) {
			$issues[] = 'Antivirus scanning not enabled for attachments';
		}
		
		// Check 6: Verify attachment access restrictions
		$access_control = get_option( 'wpforo_attachments_access_control', false );
		if ( ! $access_control ) {
			$issues[] = 'Attachment access control not configured';
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
					'Found %d wpForo attachment security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforo-attachment-security',
			);
		}
		
		return null;
	}
}
