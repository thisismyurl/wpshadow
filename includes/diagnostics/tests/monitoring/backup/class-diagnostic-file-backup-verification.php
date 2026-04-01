<?php
/**
 * File Backup Verification Diagnostic
 *
 * Checks if file backups include all important directories.
 *
 * @package    WPShadow
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
 * File Backup Verification Diagnostic Class
 *
 * Verifies file backups cover all important site files.
 * Like checking that you're backing up all your important folders.
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Backup_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-backup-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Backup Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file backups include all important directories';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the file backup verification diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if file backup issues detected, null otherwise.
	 */
	public static function check() {
		// Important directories that should be backed up.
		$important_dirs = array(
			'wp-content/uploads'  => __( 'Media files (images, videos, documents)', 'wpshadow' ),
			'wp-content/plugins'  => __( 'Installed plugins', 'wpshadow' ),
			'wp-content/themes'   => __( 'Installed themes', 'wpshadow' ),
		);

		$missing_from_backup = array();
		$backup_configured = false;

		// Check UpdraftPlus.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$backup_configured = true;
			$include_uploads = get_option( 'updraft_include_uploads', 1 );
			$include_plugins = get_option( 'updraft_include_plugins', 1 );
			$include_themes = get_option( 'updraft_include_themes', 1 );

			if ( ! $include_uploads ) {
				$missing_from_backup[] = array(
					'dir'  => 'wp-content/uploads',
					'what' => $important_dirs['wp-content/uploads'],
				);
			}
			if ( ! $include_plugins ) {
				$missing_from_backup[] = array(
					'dir'  => 'wp-content/plugins',
					'what' => $important_dirs['wp-content/plugins'],
				);
			}
			if ( ! $include_themes ) {
				$missing_from_backup[] = array(
					'dir'  => 'wp-content/themes',
					'what' => $important_dirs['wp-content/themes'],
				);
			}
		}

		// Check BackWPup.
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$backup_configured = true;
			$jobs = \BackWPup_Option::get_job_ids();

			$backs_up_files = false;
			foreach ( $jobs as $job_id ) {
				$job_tasks = \BackWPup_Option::get( $job_id, 'type' );
				if ( in_array( 'FILE', $job_tasks, true ) ) {
					$backs_up_files = true;
					break;
				}
			}

			if ( ! $backs_up_files ) {
				foreach ( $important_dirs as $dir => $description ) {
					$missing_from_backup[] = array(
						'dir'  => $dir,
						'what' => $description,
					);
				}
			}
		}

		if ( ! $backup_configured ) {
			return null; // Separate diagnostic covers no backup config.
		}

		if ( ! empty( $missing_from_backup ) ) {
			$missing_list = array();
			foreach ( $missing_from_backup as $missing ) {
				$missing_list[] = $missing['what'];
			}

			return array(
				'id'           => self::$slug . '-incomplete',
				'title'        => __( 'File Backup Incomplete', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of missing directories */
					__( 'Your backup isn\'t including all important files (like backing up only some of your photo albums). Missing from backups: %s. Without these, you can\'t fully restore your site after a disaster. Enable these in your backup plugin settings.', 'wpshadow' ),
					implode( ', ', $missing_list )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-files?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'missing' => $missing_from_backup,
				),
			);
		}

		// Check if uploads folder is very large (may cause backup issues).
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		if ( is_dir( $uploads_path ) ) {
			$uploads_size = self::get_directory_size( $uploads_path );
			$uploads_gb = $uploads_size / 1024 / 1024 / 1024;

			if ( $uploads_gb > 10 ) {
				return array(
					'id'           => self::$slug . '-large-uploads',
					'title'        => __( 'Very Large Uploads Folder', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: uploads folder size */
						__( 'Your uploads folder is %s (like having a massive photo collection). Large uploads can cause backup timeouts or storage issues. Consider: using a CDN to offload files, archiving old media, or using incremental backups. Verify your backups complete successfully with this much data.', 'wpshadow' ),
						size_format( $uploads_size )
					),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-large-files?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'uploads_size' => $uploads_size,
						'uploads_gb'   => $uploads_gb,
					),
				);
			}
		}

		return null; // File backups are properly configured.
	}

	/**
	 * Get directory size (sampling method for performance).
	 *
	 * @since 0.6093.1200
	 * @param  string $dir Directory path.
	 * @return int Size in bytes (estimated).
	 */
	private static function get_directory_size( $dir ) {
		// Use cached value if available.
		$cache_key = 'wpshadow_dir_size_' . md5( $dir );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		// Sample-based size estimation (faster than full scan).
		$size = 0;
		$count = 0;
		$max_files = 100; // Sample size.

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
					++$count;

					if ( $count >= $max_files ) {
						break;
					}
				}
			}
		} catch ( \Exception $e ) {
			return 0;
		}

		// Extrapolate from sample (very rough estimate).
		if ( $count >= $max_files ) {
			$size = $size * 10; // Assume sample is ~10% of total.
		}

		// Cache for 1 day.
		set_transient( $cache_key, $size, DAY_IN_SECONDS );

		return $size;
	}
}
