<?php
/**
 * Offsite Backup Storage Diagnostic
 *
 * Checks if backups are stored offsite (not just local).
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
 * Offsite Backup Storage Diagnostic Class
 *
 * Verifies backups are stored in remote/offsite location.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Offsite_Backup_Storage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'offsite-backup-storage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Offsite Backup Storage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are stored offsite (not just local)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the offsite backup storage diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if offsite backup not configured, null otherwise.
	 */
	public static function check() {
		$has_offsite = self::check_offsite_storage();

		if ( ! $has_offsite ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No offsite backup storage detected. Store backups in cloud (AWS, Google Cloud, Dropbox, etc.) to protect against server failure.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/configure-offsite-backups',
			);
		}

		return null;
	}

	/**
	 * Check for offsite backup storage configuration.
	 *
	 * @since 1.6093.1200
	 * @return bool True if offsite storage configured.
	 */
	private static function check_offsite_storage(): bool {
		// Check UpdraftPlus remote storage.
		$updraftplus_settings = get_option( 'updraftplus_options' );
		if ( $updraftplus_settings && is_array( $updraftplus_settings ) ) {
			$remote_methods = array(
				's3',
				'dropbox',
				'googledrive',
				'azure',
				'sftp',
				'ftp',
				'b2',
			);

			foreach ( $remote_methods as $method ) {
				if ( isset( $updraftplus_settings[ 'updraftplus_' . $method ] ) ) {
					$setting = $updraftplus_settings[ 'updraftplus_' . $method ];
					if ( $setting && 'on' === $setting ) {
						return true;
					}
				}
			}
		}

		// Check BackWPup remote storage.
		$backwpup_jobs = get_option( 'backwpup_jobs' );
		if ( $backwpup_jobs && is_array( $backwpup_jobs ) ) {
			foreach ( $backwpup_jobs as $job ) {
				$backup_to_remote = array(
					'backup_to_s3',
					'backup_to_dropbox',
					'backup_to_gdrive',
					'backup_to_azure',
					'backup_to_sftp',
					'backup_to_ftp',
				);

				foreach ( $backup_to_remote as $remote_option ) {
					if ( isset( $job[ $remote_option ] ) && $job[ $remote_option ] ) {
						return true;
					}
				}
			}
		}

		// Check WP Offload Media (backs up media to S3).
		if ( class_exists( 'AS3CF' ) ) {
			return true;
		}

		// Check Jetpack Backup.
		if ( is_plugin_active( 'jetpack-backup/jetpack-backup.php' ) ) {
			return true;
		}

		return false;
	}
}
