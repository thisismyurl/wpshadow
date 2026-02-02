<?php
/**
 * Import Files Readable by Other Users
 *
 * Detects when uploaded import files have incorrect permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Import_Files_Readable_By_Other_Users Class
 *
 * Validates import file permission security.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Import_Files_Readable_By_Other_Users extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-files-readable-by-other-users';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import File Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies import files have secure permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests import file permission security.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check import directory permissions
		$import_dir = self::get_import_directory();
		$dir_perms   = self::check_directory_permissions( $import_dir );
		if ( $dir_perms ) {
			$issues[] = $dir_perms;
		}

		// 2. Check import files permissions
		$file_perms = self::check_import_file_permissions( $import_dir );
		if ( $file_perms ) {
			$issues[] = $file_perms;
		}

		// 3. Check for world-readable files
		$world_readable = self::find_world_readable_files( $import_dir );
		if ( $world_readable ) {
			$issues[] = $world_readable;
		}

		// 4. Check shared hosting isolation
		if ( self::is_shared_hosting() ) {
			$shared_issue = self::check_shared_hosting_isolation( $import_dir );
			if ( $shared_issue ) {
				$issues[] = $shared_issue;
			}
		}

		// 5. Check for sensitive data in imports
		$sensitive_issue = self::check_sensitive_data_in_imports( $import_dir );
		if ( $sensitive_issue ) {
			$issues[] = $sensitive_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of permission issues */
					__( '%d import file permission issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/import-file-permissions',
				'recommendations' => array(
					__( 'Set import directory to 750 permissions', 'wpshadow' ),
					__( 'Set import files to 640 permissions', 'wpshadow' ),
					__( 'Move import directory outside web root', 'wpshadow' ),
					__( 'Delete old import files after processing', 'wpshadow' ),
					__( 'Use SFTP instead of FTP for uploads', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Get import directory path.
	 *
	 * @since  1.2601.2148
	 * @return string Import directory path.
	 */
	private static function get_import_directory() {
		$uploads = wp_upload_dir();
		$import_dir = $uploads['basedir'] . '/wpshadow-imports';

		return $import_dir;
	}

	/**
	 * Check directory permissions.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_directory_permissions( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return null; // Directory doesn't exist yet
		}

		$perms = substr( sprintf( '%o', fileperms( $dir ) ), -3 );

		// Should be 750 or 700 (no world access)
		if ( '750' !== $perms && '700' !== $perms ) {
			return sprintf(
				/* translators: %s: permission bits */
				__( 'Import directory has unsafe permissions: %s (should be 750)', 'wpshadow' ),
				$perms
			);
		}

		return null;
	}

	/**
	 * Check import file permissions.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_import_file_permissions( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return null;
		}

		$unsafe_files = array();
		$files        = glob( $dir . '/*.{xml,json,csv}', GLOB_BRACE );

		foreach ( (array) $files as $file ) {
			$perms = substr( sprintf( '%o', fileperms( $file ) ), -3 );

			// Should be 640 or 600 (not world-readable)
			if ( '640' !== $perms && '600' !== $perms ) {
				$unsafe_files[] = basename( $file ) . " ($perms)";
			}
		}

		if ( ! empty( $unsafe_files ) ) {
			return sprintf(
				/* translators: %d: number of files, %s: file list */
				__( '%d import files have unsafe permissions: %s', 'wpshadow' ),
				count( $unsafe_files ),
				implode( ', ', array_slice( $unsafe_files, 0, 3 ) )
			);
		}

		return null;
	}

	/**
	 * Find world-readable files.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function find_world_readable_files( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return null;
		}

		$world_readable = array();
		$files          = glob( $dir . '/*.{xml,json,csv}', GLOB_BRACE );

		foreach ( (array) $files as $file ) {
			$perms = substr( sprintf( '%o', fileperms( $file ) ), -1 );

			// Last digit: if 4 or higher, file is world-readable
			if ( (int) $perms >= 4 ) {
				$world_readable[] = basename( $file );
			}
		}

		if ( ! empty( $world_readable ) ) {
			return sprintf(
				/* translators: %d: number of world-readable files */
				__( '%d import files are world-readable (exposed to all users)', 'wpshadow' ),
				count( $world_readable )
			);
		}

		return null;
	}

	/**
	 * Check if on shared hosting.
	 *
	 * @since  1.2601.2148
	 * @return bool True if likely shared hosting.
	 */
	private static function is_shared_hosting() {
		// Check for typical shared hosting indicators
		$indicators = array(
			'cpanel',
			'plesk',
			'directadmin',
			'interworx',
			'cloudlinux',
		);

		$uname = php_uname( 's' );

		foreach ( $indicators as $indicator ) {
			if ( stripos( $uname, $indicator ) !== false ) {
				return true;
			}
		}

		// Check file owner
		if ( function_exists( 'posix_getuid' ) ) {
			$owner = posix_getuid();
			// Different UID than running process = shared hosting
			if ( $owner !== fileowner( ABSPATH ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check shared hosting isolation.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_shared_hosting_isolation( $dir ) {
		// On shared hosting, other users might access files
		if ( ! is_dir( $dir ) ) {
			return null;
		}

		// Check if directory is in public_html
		if ( strpos( $dir, 'public_html' ) !== false || strpos( $dir, 'www' ) !== false ) {
			return __( 'Import directory in web-accessible location (shared hosting risk)', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for sensitive data in imports.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_sensitive_data_in_imports( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return null;
		}

		// Check file size (large files = more sensitive data)
		$files = glob( $dir . '/*.{xml,json,csv}', GLOB_BRACE );
		$total_size = 0;

		foreach ( (array) $files as $file ) {
			$total_size += filesize( $file );
		}

		// If more than 100MB of import data with insecure permissions
		if ( $total_size > 104857600 ) {
			return sprintf(
				/* translators: %s: size in MB */
				__( '%sMB of import data stored with suboptimal permissions', 'wpshadow' ),
				round( $total_size / 1048576, 1 )
			);
		}

		return null;
	}
}
