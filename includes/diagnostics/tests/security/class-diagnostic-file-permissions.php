<?php
/**
 * File Permissions Diagnostic
 *
 * Checks critical WordPress files and directories for unsafe permission modes
 * that could allow unauthorised reading or modification of site files.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_Permissions Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Permissions extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'file-permissions';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'File Permissions';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks wp-config.php, .htaccess, and the uploads directory for world-readable or world-writable permission modes that could expose credentials or allow code injection.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Inspects fileperms() for wp-config.php (world-readable or world-writable),
	 * .htaccess (world-writable), and the uploads directory (777). Flags any
	 * violations with the specific file path and detected mode.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$issues = array();

		// wp-config.php: should not be world-readable (others bit).
		$wpconfig_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $wpconfig_path ) ) {
			$perms = fileperms( $wpconfig_path ) & 0777;
			if ( $perms & 0004 ) { // World-readable.
				$issues[] = sprintf( 'wp-config.php is world-readable (mode: %04o)', $perms );
			}
			if ( $perms & 0002 ) { // World-writable.
				$issues[] = sprintf( 'wp-config.php is world-writable (mode: %04o)', $perms );
			}
		}

		// .htaccess: should not be world-writable.
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$perms = fileperms( $htaccess_path ) & 0777;
			if ( $perms & 0002 ) {
				$issues[] = sprintf( '.htaccess is world-writable (mode: %04o)', $perms );
			}
		}

		// Uploads directory: 0777 is too permissive.
		$upload_dir    = wp_upload_dir();
		$uploads_base  = $upload_dir['basedir'];
		if ( is_dir( $uploads_base ) ) {
			$perms = fileperms( $uploads_base ) & 0777;
			if ( $perms === 0777 ) {
				$issues[] = sprintf( 'wp-content/uploads/ has 777 permissions (mode: %04o)', $perms );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of permission issues */
				__( 'Unsafe file permissions were detected: %s. Overly permissive file permissions allow other server users or processes to read credentials or inject code. Recommended permissions: 600 for wp-config.php, 644 for .htaccess, and 755 for directories.', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'details'      => array(
				'permission_issues' => $issues,
			),
		);
	}
}
