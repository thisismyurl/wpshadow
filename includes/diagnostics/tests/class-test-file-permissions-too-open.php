<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: File Permissions Too Open
 *
 * Detects when WordPress files have permissions that are too permissive (e.g., 777).
 * Overly open permissions expose files to unauthorized modification.
 *
 * @since 1.2.0
 */
class Test_File_Permissions_Too_Open extends Diagnostic_Base {


	/**
	 * Check for overly open file permissions
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$permission_issues = self::check_permissions();

		if ( empty( $permission_issues ) ) {
			return null;
		}

		$threat = count( $permission_issues ) * 10;
		$threat = min( 85, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'red',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d files/directories with overly open permissions',
				count( $permission_issues )
			),
			'metadata'      => array(
				'issues_count'      => count( $permission_issues ),
				'problematic_files' => array_slice( $permission_issues, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/file-permissions-security/',
			'training_link' => 'https://wpshadow.com/training/wordpress-security-hardening/',
		);
	}

	/**
	 * Guardian Sub-Test: WordPress root directory permissions
	 *
	 * @return array Test result
	 */
	public static function test_root_permissions(): array {
		$wp_root = ABSPATH;
		$perms   = self::get_file_perms( $wp_root );
		$is_safe = ! self::is_permission_too_open( $perms );

		return array(
			'test_name'   => 'WordPress Root Permissions',
			'path'        => $wp_root,
			'permissions' => $perms,
			'passed'      => $is_safe,
			'description' => $is_safe ? 'Safe permissions' : 'Permissions too open (risk)',
		);
	}

	/**
	 * Guardian Sub-Test: wp-content directory permissions
	 *
	 * @return array Test result
	 */
	public static function test_wp_content_permissions(): array {
		$wp_content = WP_CONTENT_DIR;
		$perms      = self::get_file_perms( $wp_content );
		$is_safe    = ! self::is_permission_too_open( $perms );

		return array(
			'test_name'   => 'wp-content Directory Permissions',
			'path'        => $wp_content,
			'permissions' => $perms,
			'passed'      => $is_safe,
			'description' => $is_safe ? 'Safe permissions' : 'Permissions too open (risk)',
		);
	}

	/**
	 * Guardian Sub-Test: wp-config.php permissions
	 *
	 * @return array Test result
	 */
	public static function test_wp_config_permissions(): array {
		$wp_config = ABSPATH . 'wp-config.php';
		$exists    = file_exists( $wp_config );
		$perms     = $exists ? self::get_file_perms( $wp_config ) : null;
		$is_safe   = $exists ? ! self::is_permission_too_open( $perms ) : false;

		return array(
			'test_name'   => 'wp-config.php Permissions',
			'path'        => $wp_config,
			'exists'      => $exists,
			'permissions' => $perms ?? 'N/A',
			'passed'      => $is_safe,
			'description' => $exists ? ( $is_safe ? 'Safe permissions' : 'CRITICAL: wp-config.php too open' ) : 'wp-config.php not found',
		);
	}

	/**
	 * Guardian Sub-Test: .htaccess permissions
	 *
	 * @return array Test result
	 */
	public static function test_htaccess_permissions(): array {
		$htaccess = ABSPATH . '.htaccess';
		$exists   = file_exists( $htaccess );
		$perms    = $exists ? self::get_file_perms( $htaccess ) : null;
		$is_safe  = $exists ? ! self::is_permission_too_open( $perms ) : false;

		return array(
			'test_name'   => '.htaccess Permissions',
			'path'        => $htaccess,
			'exists'      => $exists,
			'permissions' => $perms ?? 'N/A',
			'passed'      => $is_safe || ! $exists,
			'description' => $exists ? ( $is_safe ? 'Safe permissions' : 'Permissions too open' ) : '.htaccess not found (optional)',
		);
	}

	/**
	 * Guardian Sub-Test: All problematic files
	 *
	 * @return array Test result
	 */
	public static function test_all_issues(): array {
		$issues = self::check_permissions();

		return array(
			'test_name'         => 'All Permission Issues',
			'issue_count'       => count( $issues ),
			'problematic_files' => $issues,
			'passed'            => empty( $issues ),
			'description'       => empty( $issues ) ? 'No permission issues found' : sprintf( 'Found %d files with unsafe permissions', count( $issues ) ),
		);
	}

	/**
	 * Check for files with overly open permissions
	 *
	 * @return array List of problematic files
	 */
	private static function check_permissions(): array {
		$issues = array();

		$files_to_check = array(
			ABSPATH                   => 'WordPress root',
			WP_CONTENT_DIR            => 'wp-content directory',
			ABSPATH . 'wp-config.php' => 'wp-config.php',
			ABSPATH . '.htaccess'     => '.htaccess',
			ABSPATH . 'wp-admin'      => 'wp-admin directory',
			ABSPATH . 'wp-includes'   => 'wp-includes directory',
		);

		foreach ( $files_to_check as $path => $description ) {
			if ( ! file_exists( $path ) && is_file( $path ) ) {
				continue; // File doesn't exist
			}

			$perms = self::get_file_perms( $path );

			if ( self::is_permission_too_open( $perms ) ) {
				$issues[] = array(
					'path'           => $path,
					'description'    => $description,
					'permissions'    => $perms,
					'numeric'        => substr( sprintf( '%o', fileperms( $path ) ), -4 ),
					'recommendation' => self::get_safe_permissions( $path ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Get file permissions string
	 *
	 * @param string $path File or directory path
	 * @return string Permissions string (e.g., "drwxr-xr-x")
	 */
	private static function get_file_perms( string $path ): string {
		if ( ! file_exists( $path ) ) {
			return 'N/A';
		}

		$perms  = fileperms( $path );
		$is_dir = is_dir( $path );

		// Convert to string format
		$symbolic  = '';
		$symbolic .= ( ( ( $perms & 0xC000 ) === 0xC000 ) ? 's' : // Socket
			( ( ( $perms & 0xA000 ) === 0xA000 ) ? 'l' : // Symbolic Link
				( ( ( $perms & 0x8000 ) === 0x8000 ) ? '-' : // Regular
					( ( ( $perms & 0x6000 ) === 0x6000 ) ? 'b' : // Block special
						( ( ( $perms & 0x4000 ) === 0x4000 ) ? 'd' : // Directory
							( ( ( $perms & 0x2000 ) === 0x2000 ) ? 'c' : // Character special
								( ( ( $perms & 0x1000 ) === 0x1000 ) ? 'p' : // FIFO pipe
									'u' ) ) ) ) ) ) ); // Unknown

		// Owner
		$symbolic .= ( ( $perms & 0x0100 ) ? 'r' : '-' );
		$symbolic .= ( ( $perms & 0x0080 ) ? 'w' : '-' );
		$symbolic .= ( ( $perms & 0x0040 ) ?
			( ( $perms & 0x0800 ) ? 's' : 'x' ) : ( ( $perms & 0x0800 ) ? 'S' : '-' ) );

		// Group
		$symbolic .= ( ( $perms & 0x0020 ) ? 'r' : '-' );
		$symbolic .= ( ( $perms & 0x0010 ) ? 'w' : '-' );
		$symbolic .= ( ( $perms & 0x0008 ) ?
			( ( $perms & 0x0400 ) ? 's' : 'x' ) : ( ( $perms & 0x0400 ) ? 'S' : '-' ) );

		// World
		$symbolic .= ( ( $perms & 0x0004 ) ? 'r' : '-' );
		$symbolic .= ( ( $perms & 0x0002 ) ? 'w' : '-' );
		$symbolic .= ( ( $perms & 0x0001 ) ?
			( ( $perms & 0x0200 ) ? 't' : 'x' ) : ( ( $perms & 0x0200 ) ? 'T' : '-' ) );

		return $symbolic;
	}

	/**
	 * Check if file permissions are too open
	 *
	 * @param string $perms Permission string
	 * @return bool True if too open
	 */
	private static function is_permission_too_open( string $perms ): bool {
		// Check if "other" (world) has write permissions
		if ( strlen( $perms ) > 8 ) {
			$world_write = substr( $perms, 8, 1 );
			if ( $world_write === 'w' ) {
				return true;
			}
		}

		// Check for 777 permissions (octal)
		if ( strpos( $perms, 'rwx' ) !== false && substr_count( $perms, 'rwx' ) >= 2 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get safe permissions for a path
	 *
	 * @param string $path File or directory path
	 * @return string Recommended permissions
	 */
	private static function get_safe_permissions( string $path ): string {
		if ( is_dir( $path ) ) {
			return '755 (drwxr-xr-x)';
		}

		return '644 (-rw-r--r--)';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'File Permissions Too Open';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if WordPress files have overly permissive permissions';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
