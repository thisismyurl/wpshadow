<?php
/**
 * Backup Authentication Bypass Diagnostic
 *
 * Detects authentication bypass vulnerabilities in backup/restore
 * functionality and emergency access mechanisms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Authentication Bypass Diagnostic Class
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
class Diagnostic_Backup_Authentication_Bypass extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'backup-authentication-bypass';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'Backup Authentication Bypass';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects authentication bypass in backup and restore functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans for backup authentication vulnerabilities.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Look for web-accessible backup files.
		$accessible_backups = self::find_accessible_backup_files();
		if ( ! empty( $accessible_backups ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d web-accessible backup file',
					'Found %d web-accessible backup files',
					count( $accessible_backups ),
					'wpshadow'
				),
				count( $accessible_backups )
			);
		}

		// Check 2: Check for emergency admin scripts.
		$emergency_scripts = self::find_emergency_admin_scripts();
		if ( ! empty( $emergency_scripts ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d emergency admin access script (critical security risk)', 'wpshadow' ),
				count( $emergency_scripts )
			);
		}

		// Check 3: Check backup plugin restore without nonce.
		$unsafe_restore = self::check_backup_plugin_restore_security();
		if ( $unsafe_restore ) {
			$issues[] = __( 'Backup plugin restore functionality may not verify nonces', 'wpshadow' );
		}

		// Check 4: Check for predictable backup URLs.
		$predictable_urls = self::check_predictable_backup_urls();
		if ( $predictable_urls ) {
			$issues[] = __( 'Backup files use predictable naming patterns (easy to guess URLs)', 'wpshadow' );
		}

		// Check 5: Check for .sql files in uploads.
		$sql_in_uploads = self::find_sql_files_in_uploads();
		if ( ! empty( $sql_in_uploads ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d database dump file in web-accessible directory', 'wpshadow' ),
				count( $sql_in_uploads )
			);
		}

		// Check 6: Check for .htaccess protection on backup directories.
		$unprotected_dirs = self::check_backup_directory_protection();
		if ( ! empty( $unprotected_dirs ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( '%d backup directory lacks .htaccess protection', 'wpshadow' ),
				count( $unprotected_dirs )
			);
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d backup security issue detected',
						'%d backup security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-authentication-bypass',
				'context'      => array(
					'issues'              => $issues,
					'accessible_backups'  => $accessible_backups ?? array(),
					'emergency_scripts'   => $emergency_scripts ?? array(),
					'sql_files'           => $sql_in_uploads ?? array(),
					'unprotected_dirs'    => $unprotected_dirs ?? array(),
					'why'                 => __(
						'Backup authentication bypass is catastrophic because backups contain complete site data including credentials. ' .
						'Emergency admin scripts (often created for password recovery) frequently lack proper authentication, allowing ' .
						'anyone who discovers them to gain admin access. Web-accessible backup files expose database dumps with password ' .
						'hashes, user data, and sensitive configuration. Predictable backup URLs (backup-2024-01-15.zip) are easily guessed ' .
						'by automated scanners. According to OWASP, exposed backup files are in the Top 10 because they provide complete ' .
						'site compromise in a single download. Attackers also restore malicious backups to inject backdoors.',
						'wpshadow'
					),
					'recommendation'      => __(
						'Move backup files outside web root (one directory above public_html). Use randomized filenames with tokens ' .
						'(backup-a8f3n2k1x9.zip). Add .htaccess deny rules to backup directories. Delete emergency admin scripts immediately ' .
						'after use. Require nonce verification + capability checks for restore operations. Block direct access to .sql files. ' .
						'Use encrypted backups with strong passwords. Store backups in separate cloud storage (S3, Dropbox) with authentication. ' .
						'Implement restore confirmation (email verification or 2FA). Log all backup download and restore attempts.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'vault',
				'backup-security',
				'backup-hardening-guide'
			);

			return $finding;
		}

		return null;
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
