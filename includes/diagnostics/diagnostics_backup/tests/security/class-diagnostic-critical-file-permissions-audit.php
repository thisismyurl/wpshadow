<?php
/**
 * Critical File Permissions Audit Diagnostic
 *
 * Scans critical WordPress files/folders for insecure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical File Permissions Audit Class
 *
 * Tests file permissions.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Critical_File_Permissions_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-file-permissions-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical File Permissions Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans critical WordPress files/folders for insecure permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$permissions_check = self::check_file_permissions();
		
		if ( ! empty( $permissions_check['insecure_files'] ) ) {
			$files_list = array();
			
			foreach ( $permissions_check['insecure_files'] as $file_info ) {
				$files_list[] = sprintf(
					'%s (%s)',
					$file_info['file'],
					$file_info['permissions']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of files */
					__( 'Found %d files with insecure permissions (777 or world-writable)', 'wpshadow' ),
					count( $permissions_check['insecure_files'] )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/critical-file-permissions-audit',
				'meta'         => array(
					'insecure_files' => $permissions_check['insecure_files'],
					'files_list'     => $files_list,
				),
			);
		}

		return null;
	}

	/**
	 * Check file permissions.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_file_permissions() {
		$check = array(
			'insecure_files' => array(),
		);

		// Critical files to check.
		$critical_files = array(
			ABSPATH . 'wp-config.php',
			ABSPATH . '.htaccess',
		);

		// Critical directories to check.
		$critical_dirs = array(
			WP_CONTENT_DIR . '/uploads',
			WP_CONTENT_DIR . '/plugins',
			WP_CONTENT_DIR . '/themes',
			ABSPATH . 'wp-admin',
		);

		// Check files.
		foreach ( $critical_files as $file ) {
			if ( file_exists( $file ) ) {
				$perms = fileperms( $file );
				$octal_perms = substr( sprintf( '%o', $perms ), -3 );

				// Check if world-writable (x7x or xx7).
				if ( '777' === $octal_perms || 
				     '666' === $octal_perms ||
				     '7' === substr( $octal_perms, -1 ) ) {
					$check['insecure_files'][] = array(
						'file'        => str_replace( ABSPATH, '', $file ),
						'permissions' => $octal_perms,
						'type'        => 'file',
					);
				}
			}
		}

		// Check directories.
		foreach ( $critical_dirs as $dir ) {
			if ( file_exists( $dir ) ) {
				$perms = fileperms( $dir );
				$octal_perms = substr( sprintf( '%o', $perms ), -3 );

				// Check if 777.
				if ( '777' === $octal_perms ) {
					$check['insecure_files'][] = array(
						'file'        => str_replace( ABSPATH, '', $dir ),
						'permissions' => $octal_perms,
						'type'        => 'directory',
					);
				}
			}
		}

		return $check;
	}
}
