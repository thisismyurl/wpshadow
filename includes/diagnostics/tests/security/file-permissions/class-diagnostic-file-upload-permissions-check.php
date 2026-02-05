<?php
/**
 * File Upload Permissions Check Diagnostic
 *
 * Checks if file upload directories have appropriate permissions.
 *
 * @package WPShadow\Diagnostics
 * @since   1.6032.0147
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: File Upload Permissions Check
 *
 * Detects potential security issues with file upload directory permissions.
 */
class Diagnostic_File_Upload_Permissions_Check extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-permissions-check';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Permissions Check';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates file upload directory permissions';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		// Get WordPress upload directory
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		$stats['upload_directory'] = $upload_path;

		// Check if directory exists and is writable
		if ( ! is_dir( $upload_path ) ) {
			$issues[] = __( 'Upload directory does not exist', 'wpshadow' );
		} elseif ( ! is_writable( $upload_path ) ) {
			$issues[] = __( 'Upload directory is not writable', 'wpshadow' );
		}

		// Check directory permissions (octal)
		if ( file_exists( $upload_path ) ) {
			$perms = substr( sprintf( '%o', fileperms( $upload_path ) ), -4 );
			$stats['upload_permissions'] = $perms;

			// Check if permissions are too restrictive or too permissive
			if ( ! in_array( $perms, array( '0755', '0775', '0777' ), true ) ) {
				$issues[] = __( 'Upload directory has unusual permissions', 'wpshadow' );
			}
		}

		// Check for .htaccess in uploads directory
		$htaccess_path = trailingslashit( $upload_path ) . '.htaccess';
		$stats['htaccess_exists'] = file_exists( $htaccess_path );

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Proper file permissions protect against unauthorized file uploads and execution. Upload directories should be writable but not executable, preventing attackers from uploading and running malicious files.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-permissions',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
