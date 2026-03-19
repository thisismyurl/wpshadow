<?php
/**
 * Upload Directory Permissions Diagnostic
 *
 * Verifies wp-content/uploads directory has correct write permissions. Tests
 * directory creation and file write capabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Directory Permissions Diagnostic Class
 *
 * Checks for permission issues in the uploads directory.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates wp-content/uploads directory permissions and write capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get upload directory info.
		$upload_dir = wp_upload_dir();

		if ( $upload_dir['error'] ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Upload directory error: %s', 'wpshadow' ),
				$upload_dir['error']
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-directory-permissions',
			);
		}

		$base_dir = $upload_dir['basedir'];
		$current_dir = $upload_dir['path'];

		// Check if base directory exists.
		if ( ! file_exists( $base_dir ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory does not exist: %s', 'wpshadow' ),
				$base_dir
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-directory-permissions',
			);
		}

		// Check if base directory is writable.
		if ( ! wp_is_writable( $base_dir ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory is not writable: %s', 'wpshadow' ),
				$base_dir
			);
		}

		// Check permissions on base directory.
		$perms = fileperms( $base_dir );
		$octal_perms = substr( sprintf( '%o', $perms ), -4 );

		// Ideal: 0755 (owner: rwx, group: rx, other: rx).
		// Acceptable: 0775 (owner: rwx, group: rwx, other: rx).
		// Too open: 0777 (security risk).
		// Too restrictive: 0644, 0744.
		if ( '0777' === $octal_perms ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory has 0777 permissions (security risk): %s', 'wpshadow' ),
				$base_dir
			);
		} elseif ( in_array( $octal_perms, array( '0644', '0744', '0444' ), true ) ) {
			$issues[] = sprintf(
				/* translators: 1: permissions, 2: directory path */
				__( 'Upload directory has %1$s permissions (too restrictive): %2$s', 'wpshadow' ),
				$octal_perms,
				$base_dir
			);
		}

		// Check if year/month subdirectories exist and are writable.
		if ( file_exists( $current_dir ) ) {
			if ( ! wp_is_writable( $current_dir ) ) {
				$issues[] = sprintf(
					/* translators: %s: directory path */
					__( 'Current upload subdirectory is not writable: %s', 'wpshadow' ),
					str_replace( $base_dir, '', $current_dir )
				);
			}
		} else {
			// Try to create the current directory.
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			if ( ! $wp_filesystem->mkdir( $current_dir, FS_CHMOD_DIR ) ) {
				$issues[] = sprintf(
					/* translators: %s: directory path */
					__( 'Cannot create upload subdirectory: %s', 'wpshadow' ),
					str_replace( $base_dir, '', $current_dir )
				);
			}
		}

		// Test actual write capability with a temporary file.
		$test_file = $current_dir . '/wpshadow-test-' . time() . '.txt';
		$test_content = 'WPShadow permission test';
		
		$write_result = @file_put_contents( $test_file, $test_content );
		
		if ( false === $write_result ) {
			$issues[] = __( 'Cannot write test file to upload directory (permission denied)', 'wpshadow' );
		} else {
			// Clean up test file.
			@unlink( $test_file );
		}

		// Check ownership (if on Unix/Linux).
		if ( function_exists( 'posix_getuid' ) ) {
			$php_user = posix_getuid();
			$dir_owner = fileowner( $base_dir );
			
			if ( $php_user !== $dir_owner ) {
				$php_user_info = posix_getpwuid( $php_user );
				$dir_owner_info = posix_getpwuid( $dir_owner );
				
				if ( $php_user_info && $dir_owner_info ) {
					$issues[] = sprintf(
						/* translators: 1: PHP user, 2: directory owner */
						__( 'Upload directory owner (%2$s) differs from PHP process user (%1$s)', 'wpshadow' ),
						$php_user_info['name'],
						$dir_owner_info['name']
					);
				}
			}
		}

		// Check for .htaccess file in uploads (security).
		$htaccess_file = $base_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$issues[] = __( 'No .htaccess file in uploads directory (consider adding for security)', 'wpshadow' );
		} else {
			// Check if .htaccess has proper rules.
			$htaccess_content = @file_get_contents( $htaccess_file );
			if ( $htaccess_content ) {
				// Should deny PHP execution.
				if ( strpos( $htaccess_content, 'php' ) === false || 
				     strpos( $htaccess_content, 'deny' ) === false ) {
					$issues[] = __( 'Upload .htaccess does not deny PHP execution (security risk)', 'wpshadow' );
				}
			}
		}

		// Check for index.php in uploads (prevent directory listing).
		$index_file = $base_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			$issues[] = __( 'No index.php in uploads directory (directory listing possible)', 'wpshadow' );
		}

		// Check if uploads directory is inside web root.
		$abspath_real = realpath( ABSPATH );
		$upload_real = realpath( $base_dir );
		
		if ( $abspath_real && $upload_real && strpos( $upload_real, $abspath_real ) !== 0 ) {
			$issues[] = __( 'Upload directory is outside WordPress installation (unusual configuration)', 'wpshadow' );
		}

		// Check for excessive subdirectories (performance).
		$subdirs = glob( $base_dir . '/*', GLOB_ONLYDIR );
		if ( is_array( $subdirs ) && count( $subdirs ) > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of subdirectories */
				__( '%d subdirectories in uploads (consider cleanup)', 'wpshadow' ),
				count( $subdirs )
			);
		}

		// Check if directory is accessible via HTTP (should be).
		$upload_url = $upload_dir['baseurl'] . '/wpshadow-test.txt';
		$local_file = $base_dir . '/wpshadow-test.txt';
		
		@file_put_contents( $local_file, 'test' );
		
		$response = wp_remote_get( $upload_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) ) {
			$issues[] = __( 'Upload directory may not be accessible via HTTP (check server config)', 'wpshadow' );
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP response code */
				__( 'Upload directory returned HTTP %d (should be 200)', 'wpshadow' ),
				wp_remote_retrieve_response_code( $response )
			);
		}
		
		@unlink( $local_file );

		// Check for uploads directory constant override.
		if ( defined( 'UPLOADS' ) ) {
			$custom_uploads = UPLOADS;
			if ( strpos( $custom_uploads, '..' ) !== false ) {
				$issues[] = __( 'UPLOADS constant uses relative path (potential security issue)', 'wpshadow' );
			}
		}

		// Check for symbolic links (security concern).
		if ( is_link( $base_dir ) ) {
			$issues[] = __( 'Upload directory is a symbolic link (verify destination)', 'wpshadow' );
		}

		// Check available disk space percentage.
		$disk_free = @disk_free_space( $base_dir );
		$disk_total = @disk_total_space( $base_dir );
		
		if ( $disk_free !== false && $disk_total !== false && $disk_total > 0 ) {
			$percent_free = ( $disk_free / $disk_total ) * 100;
			
			if ( $percent_free < 5 ) {
				$issues[] = sprintf(
					/* translators: %s: percentage */
					__( 'Upload disk only %s%% free (critical)', 'wpshadow' ),
					number_format( $percent_free, 1 )
				);
			} elseif ( $percent_free < 10 ) {
				$issues[] = sprintf(
					/* translators: %s: percentage */
					__( 'Upload disk only %s%% free (low)', 'wpshadow' ),
					number_format( $percent_free, 1 )
				);
			}
		}

		// Check for open_basedir restrictions.
		$open_basedir = ini_get( 'open_basedir' );
		if ( $open_basedir ) {
			$allowed_paths = explode( PATH_SEPARATOR, $open_basedir );
			$upload_allowed = false;
			
			foreach ( $allowed_paths as $path ) {
				if ( strpos( $base_dir, rtrim( $path, '/' ) ) === 0 ) {
					$upload_allowed = true;
					break;
				}
			}
			
			if ( ! $upload_allowed ) {
				$issues[] = __( 'Upload directory restricted by open_basedir (uploads will fail)', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-directory-permissions',
			);
		}

		return null;
	}
}
