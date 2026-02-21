<?php
/**
 * Backup Authentication Bypass Treatment
 *
 * Detects authentication bypass vulnerabilities in backup/restore
 * functionality and emergency access mechanisms.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Authentication Bypass Treatment Class
 *
 * Checks for:
 * - Backup files accessible without authentication
 * - Emergency admin access scripts
 * - Restore functionality without verification
 * - Backup URLs with predictable patterns
 * - Database dumps in web-accessible directories
 * - .sql files in /wp-content/
 *
 * Backup authentication bypass allows attackers to access sensitive
 * data or restore malicious backups without proper authentication.
 *
 * @since 1.2033.2108
 */
class Treatment_Backup_Authentication_Bypass extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'backup-authentication-bypass';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'Backup Authentication Bypass';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects authentication bypass in backup and restore functionality';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans for backup authentication vulnerabilities.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Backup_Authentication_Bypass' );
	}

	/**
	 * Find web-accessible backup files.
	 *
	 * @since  1.2033.2108
	 * @return array Backup file paths.
	 */
	private static function find_accessible_backup_files() {
		$found = array();
		$extensions = array( '.zip', '.tar', '.tar.gz', '.tgz', '.sql', '.backup' );
		
		// Check common backup locations.
		$check_dirs = array(
			WP_CONTENT_DIR,
			WP_CONTENT_DIR . '/uploads',
			ABSPATH . 'backups',
			ABSPATH . 'backup',
		);

		foreach ( $check_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Scan for backup files (limit 50 to avoid timeout).
			$count = 0;
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $count >= 50 ) {
					break;
				}

				if ( ! $file->isFile() ) {
					continue;
				}

				$filename = $file->getFilename();
				foreach ( $extensions as $ext ) {
					if ( str_ends_with( $filename, $ext ) && str_contains( $filename, 'backup' ) ) {
						$found[] = str_replace( ABSPATH, '', $file->getPathname() );
						$count++;
						break;
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Find emergency admin scripts.
	 *
	 * @since  1.2033.2108
	 * @return array Script paths.
	 */
	private static function find_emergency_admin_scripts() {
		$found = array();
		$patterns = array( 'emergency', 'admin-reset', 'password-reset', 'backdoor', 'recover' );

		// Scan root directory only.
		$files = glob( ABSPATH . '*.php' );
		foreach ( $files as $file ) {
			$filename = basename( $file );
			
			foreach ( $patterns as $pattern ) {
				if ( str_contains( strtolower( $filename ), $pattern ) ) {
					// Check if file creates admin user or bypasses auth.
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$content = file_get_contents( $file );
					if ( str_contains( $content, 'wp_create_user' ) || 
					     str_contains( $content, 'wp_insert_user' ) ||
					     str_contains( $content, "role = 'administrator'" ) ) {
						$found[] = str_replace( ABSPATH, '', $file );
						break;
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Check backup plugin restore security.
	 *
	 * @since  1.2033.2108
	 * @return bool True if unsafe.
	 */
	private static function check_backup_plugin_restore_security() {
		$backup_plugins = array(
			'updraftplus',
			'backupwordpress',
			'backwpup',
			'duplicator',
			'all-in-one-wp-migration',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		
		foreach ( $backup_plugins as $plugin ) {
			foreach ( $active_plugins as $active ) {
				if ( str_contains( $active, $plugin ) ) {
					// Found backup plugin, assume it has restore functionality.
					// Check if restore actions use nonces (simplified check).
					global $wp_filter;
					if ( isset( $wp_filter['wp_ajax_' . $plugin . '_restore'] ) ) {
						return true; // Potential risk if AJAX restore exists.
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check for predictable backup naming.
	 *
	 * @since  1.2033.2108
	 * @return bool True if predictable.
	 */
	private static function check_predictable_backup_urls() {
		$backup_dir = WP_CONTENT_DIR . '/uploads';
		
		if ( ! is_dir( $backup_dir ) ) {
			return false;
		}

		// Look for date-based backup patterns.
		$pattern = '/backup[_-]\d{4}[_-]\d{2}[_-]\d{2}\.(zip|tar|gz)/i';
		
		$files = glob( $backup_dir . '/*.{zip,tar,gz}', GLOB_BRACE );
		foreach ( $files as $file ) {
			if ( preg_match( $pattern, basename( $file ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find SQL files in uploads.
	 *
	 * @since  1.2033.2108
	 * @return array SQL file paths.
	 */
	private static function find_sql_files_in_uploads() {
		$found = array();
		$upload_dir = wp_upload_dir();
		$basedir = $upload_dir['basedir'];

		if ( ! is_dir( $basedir ) ) {
			return $found;
		}

		$sql_files = glob( $basedir . '/*.sql' );
		foreach ( $sql_files as $file ) {
			$found[] = str_replace( ABSPATH, '', $file );
		}

		return $found;
	}

	/**
	 * Check backup directory protection.
	 *
	 * @since  1.2033.2108
	 * @return array Unprotected directories.
	 */
	private static function check_backup_directory_protection() {
		$unprotected = array();
		$backup_dirs = array(
			ABSPATH . 'backups',
			ABSPATH . 'backup',
			WP_CONTENT_DIR . '/backups',
			WP_CONTENT_DIR . '/ai1wm-backups',
		);

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$htaccess = $dir . '/.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				$unprotected[] = str_replace( ABSPATH, '', $dir );
			}
		}

		return $unprotected;
	}
}
