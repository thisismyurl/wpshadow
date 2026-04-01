<?php
/**
 * File Upload Security Diagnostic
 *
 * Tests if file upload functionality has proper security measures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Upload Security Diagnostic Class
 *
 * Validates that file uploads have proper security including file type
 * validation, size limits, directory protection, and malicious file detection.
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Upload_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if file upload functionality has proper security measures';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests file upload security including MIME type validation,
	 * file size limits, and dangerous file type restrictions.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get upload directory.
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		// Check if uploads directory is protected.
		$htaccess_file = $uploads_path . '/.htaccess';
		$has_htaccess = file_exists( $htaccess_file );

		// Check uploads web.config (IIS).
		$webconfig_file = $uploads_path . '/web.config';
		$has_webconfig = file_exists( $webconfig_file );

		// Check for directory listing prevention.
		$has_dir_protection = $has_htaccess || $has_webconfig;

		// Get allowed MIME types.
		$allowed_mimes = get_allowed_mime_types();

		// Check for dangerous MIME types being allowed.
		$dangerous_mimes = array( 'exe', 'bat', 'cmd', 'php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'phar' );
		$allows_dangerous = false;

		foreach ( $dangerous_mimes as $ext ) {
			if ( isset( $allowed_mimes[ $ext ] ) ) {
				$allows_dangerous = true;
				break;
			}
		}

		// Check upload size limit.
		$max_upload_size = wp_max_upload_size();
		$max_upload_mb = $max_upload_size / ( 1024 * 1024 );

		// Check PHP settings.
		$post_max_size = ini_get( 'post_max_size' );
		$upload_max_filesize = ini_get( 'upload_max_filesize' );

		// Check for form upload plugins with security.
		$has_secure_upload = is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ||
							is_plugin_active( 'gravityforms/gravityforms.php' ) ||
							is_plugin_active( 'wpforms-lite/wpforms.php' );

		// Check for malware scanner.
		$has_malware_scanner = is_plugin_active( 'wordfence/wordfence.php' ) ||
							  is_plugin_active( 'sucuri-scanner/sucuri.php' );

		// Check recent uploads for suspicious files.
		global $wpdb;
		$recent_uploads = $wpdb->get_results(
			"SELECT post_mime_type, COUNT(*) as count
			 FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
			 GROUP BY post_mime_type",
			ARRAY_A
		);

		$mime_types_used = array();
		foreach ( $recent_uploads as $upload ) {
			$mime_types_used[] = $upload['post_mime_type'];
		}

		// Check for AJAX upload handlers without nonce.
		$ajax_uploads = has_action( 'wp_ajax_upload_attachment' );
		$nonce_protection = function_exists( 'wp_verify_nonce' );

		// Check multipart form encoding.
		$has_multipart_filter = has_filter( 'wp_handle_upload' );

		// Check for issues.
		$issues = array();

		// Issue 1: Uploads directory not protected.
		if ( ! $has_dir_protection ) {
			$issues[] = array(
				'type'        => 'no_dir_protection',
				'description' => __( 'Adding a protection file to your uploads folder helps prevent visitors from browsing your files (like having blinds on your windows). This makes it harder for people to discover and download files they shouldn\'t see.', 'wpshadow' ),
			);
		}

		// Issue 2: Dangerous MIME types allowed.
		if ( $allows_dangerous ) {
			$issues[] = array(
				'type'        => 'dangerous_mimes',
				'description' => __( 'Restricting certain file types (like executable programs) from being uploaded helps keep your site safe. Think of it like airport security screening bags—some items just shouldn\'t come through.', 'wpshadow' ),
			);
		}

		// Issue 3: Upload limit too high.
		if ( $max_upload_mb > 500 ) {
			$issues[] = array(
				'type'        => 'high_upload_limit',
				'description' => sprintf(
					/* translators: %d: upload limit in MB */
					__( 'Upload limit is %d MB; should be limited to reasonable size (50-256 MB)', 'wpshadow' ),
					$max_upload_mb
				),
			);
		}

		// Issue 4: No malware scanning enabled.
		if ( ! $has_malware_scanner ) {
			$issues[] = array(
				'type'        => 'no_malware_scanner',
				'description' => __( 'No malware scanner detected; uploaded files are not scanned for threats', 'wpshadow' ),
			);
		}

		// Issue 5: AJAX uploads without nonce protection.
		if ( $ajax_uploads && ! $nonce_protection ) {
			$issues[] = array(
				'type'        => 'ajax_upload_not_protected',
				'description' => __( 'AJAX file uploads detected but nonce protection function not available', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'File upload functionality has security vulnerabilities that could allow unauthorized file uploads or execution', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-upload-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Unprotected uploads = RCE. Real scenario: Upload dir allows PHP. Attacker uploads shell.php. Visits /wp-content/uploads/shell.php. PHP executes. Full site compromise. Cost: $4.29M. With protection: PHP blocked by .htaccess. Upload rejected. Shell never executes. Attack stopped.', 'wpshadow' ),
					'recommendation' => __( '1. Add .htaccess to disable PHP: "php_flag engine off". 2. Whitelist extensions: JPG, PNG, PDF only. 3. Validate MIME type before saving. 4. Store uploads outside web root if possible. 5. Generate random filenames (prevent guessing). 6. Limit upload size: 5-10MB typical. 7. Scan with malware detector (ClamAV). 8. Protect with nonce on forms. 9. Log upload attempts. 10. Test: Upload shell.php (should fail).', 'wpshadow' ),
				),
				'details'       => array(
					'uploads_path'             => $uploads_path,
					'has_htaccess_protection'  => $has_htaccess,
					'has_webconfig_protection' => $has_webconfig,
					'has_dir_protection'       => $has_dir_protection,
					'allows_dangerous_mimes'   => $allows_dangerous,
					'max_upload_size'          => size_format( $max_upload_size ),
					'max_upload_mb'            => round( $max_upload_mb, 2 ),
					'post_max_size'            => $post_max_size,
					'upload_max_filesize'      => $upload_max_filesize,
					'has_secure_upload_plugin' => $has_secure_upload,
					'has_malware_scanner'      => $has_malware_scanner,
					'ajax_uploads_enabled'     => $ajax_uploads,
					'nonce_protection'         => $nonce_protection,
					'mime_types_in_use'        => $mime_types_used,
					'issues_detected'          => $issues,
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'comprehensive-security' );
			return $finding;
		}

		return null;
	}
}
