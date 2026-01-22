<?php
declare(strict_types=1);
/**
 * File Permissions Diagnostic
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check file permissions for security.
 */
class Diagnostic_File_Permissions extends Diagnostic_Base {

	protected static $slug        = 'file-permissions';
	protected static $title       = 'Insecure File Permissions';
	protected static $description = 'Some files have insecure permissions that could allow unauthorized access.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check wp-config.php permissions
		$wp_config = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $wp_config ) ) {
			// Try parent directory
			$wp_config = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( file_exists( $wp_config ) ) {
			$perms = fileperms( $wp_config );
			// Check if world-readable or group-writable
			if ( ( $perms & 0020 ) || ( $perms & 0002 ) ) {
				$issues[] = 'wp-config.php has insecure permissions (should be 400 or 440)';
			}
		}

		// Check wp-content directory writability
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			$issues[] = 'wp-content directory is not writable (needed for uploads and plugins)';
		}

		if ( ! empty( $issues ) ) {
			return array(
				'title'       => self::$title,
				'description' => implode( '. ', $issues ) . '. Fix file permissions via FTP or SSH.',
				'severity'    => 'medium',
				'category'    => 'security',
			);
		}

		return null;
	}
}
