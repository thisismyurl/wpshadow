<?php
/**
 * Import Files Readable by Other Users Diagnostic
 *
 * Detects when uploaded import files have incorrect permissions allowing unauthorized access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since 0.6093.1200
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
 * Verifies that import files have secure permissions.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Checks if uploaded import files have secure permissions preventing unauthorized access';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check uploads directory permissions.
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		if ( file_exists( $base_dir ) ) {
			$perms = fileperms( $base_dir );

			// Check if world-readable (0755 or 0777).
			if ( $perms && ( $perms & 0x0004 ) ) {
				// World-readable is common, but check if world-writable.
				if ( $perms & 0x0002 ) {
					$issues[] = __( 'Uploads directory is world-writable (777) - serious security risk', 'wpshadow' );
				}
			}
		}

		// 2. Check for import files in uploads.
		$import_patterns = array(
			'*.xml',
			'*.csv',
			'*.json',
			'*.sql',
		);

		$import_files = array();
		foreach ( $import_patterns as $pattern ) {
			$files = glob( $base_dir . '/' . $pattern );
			if ( ! empty( $files ) ) {
				$import_files = array_merge( $import_files, $files );
			}
		}

		if ( ! empty( $import_files ) ) {
			$insecure_files = 0;

			foreach ( $import_files as $file ) {
				$perms = fileperms( $file );

				// Files should be 600 (owner only) or 640 (owner + group).
				if ( $perms & 0x0004 ) {
					// World-readable.
					$insecure_files++;
				}
			}

			if ( $insecure_files > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of files */
					_n(
						'%d import file has world-readable permissions',
						'%d import files have world-readable permissions',
						$insecure_files,
						'wpshadow'
					),
					$insecure_files
				);
			}
		}

		// 3. Check WordPress importer temp directory.
		$wp_temp = WP_CONTENT_DIR . '/uploads/wp-importer-temp/';

		if ( file_exists( $wp_temp ) && is_dir( $wp_temp ) ) {
			$temp_perms = fileperms( $wp_temp );

			if ( $temp_perms && ( $temp_perms & 0x0002 ) ) {
				$issues[] = __( 'WordPress importer temp directory is world-writable', 'wpshadow' );
			}

			// Check files in temp directory.
			$temp_files = glob( $wp_temp . '*' );
			if ( ! empty( $temp_files ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of files */
					__( '%d file(s) left in importer temp directory - should be cleaned', 'wpshadow' ),
					count( $temp_files )
				);
			}
		}

		// 4. Check for shared hosting indicators.
		$is_shared_hosting = false;

		// Check if running as www-data or similar (shared hosting indicator).
		$process_user = function_exists( 'posix_getpwuid' ) && function_exists( 'posix_geteuid' )
			? posix_getpwuid( posix_geteuid() )
			: null;

		if ( $process_user && isset( $process_user['name'] ) ) {
			if ( in_array( $process_user['name'], array( 'www-data', 'apache', 'nobody' ), true ) ) {
				$is_shared_hosting = true;
				$issues[]          = __( 'Running on shared hosting - file permissions are critical for security', 'wpshadow' );
			}
		}

		// 5. Check FS_CHMOD_FILE constant.
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			$issues[] = __( 'FS_CHMOD_FILE not defined - using default 0644 which may be too permissive', 'wpshadow' );
		} else {
			$chmod = FS_CHMOD_FILE;

			// Check if world-readable (0644).
			if ( $chmod & 0x0004 ) {
				$issues[] = __( 'FS_CHMOD_FILE allows world-readable files - consider 0640 for sensitive data', 'wpshadow' );
			}
		}

		// 6. Check directory permissions constant.
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			$issues[] = __( 'FS_CHMOD_DIR not defined - using default 0755 which may be too permissive', 'wpshadow' );
		} else {
			$chmod_dir = FS_CHMOD_DIR;

			// Directories should typically be 0755 or 0750.
			if ( $chmod_dir & 0x0002 ) {
				$issues[] = __( 'FS_CHMOD_DIR allows world-writable directories - serious security risk', 'wpshadow' );
			}
		}

		// 7. Test actual file creation.
		$test_file = $base_dir . '/.wpshadow-permission-test-' . time() . '.txt';
		$created   = false;

		if ( file_put_contents( $test_file, 'test' ) ) {
			$created    = true;
			$test_perms = fileperms( $test_file );

			if ( $test_perms & 0x0004 ) {
				$issues[] = __( 'Newly created files are world-readable - verify server configuration', 'wpshadow' );
			}

			// Clean up.
			wp_delete_file( $test_file );
		}

		// 8. Check for .htaccess protection in uploads.
		$htaccess_file = $base_dir . '/.htaccess';

		if ( ! file_exists( $htaccess_file ) ) {
			$issues[] = __( 'No .htaccess in uploads directory - PHP files could be executed', 'wpshadow' );
		} else {
			$htaccess_content = file_get_contents( $htaccess_file );

			// Should deny PHP execution.
			if ( false === strpos( $htaccess_content, 'php' ) ) {
				$issues[] = __( '.htaccess exists but doesn\'t restrict PHP execution', 'wpshadow' );
			}
		}

		// 9. Check for plugin-specific import directories.
		$plugin_import_dirs = array(
			WP_CONTENT_DIR . '/uploads/woocommerce_uploads/',
			WP_CONTENT_DIR . '/uploads/gravity_forms/',
			WP_CONTENT_DIR . '/uploads/wpforms/',
		);

		foreach ( $plugin_import_dirs as $dir ) {
			if ( file_exists( $dir ) && is_dir( $dir ) ) {
				$dir_perms = fileperms( $dir );

				if ( $dir_perms && ( $dir_perms & 0x0002 ) ) {
					$issues[] = sprintf(
						/* translators: %s: directory name */
						__( 'Plugin directory %s is world-writable', 'wpshadow' ),
						basename( $dir )
					);
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Import file permission issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/import-file-permissions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'           => $issues,
				'is_shared_hosting' => $is_shared_hosting,
				'fs_chmod_file'    => defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : null,
			),
		);
	}
}
