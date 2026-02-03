<?php
/**
 * Offsite Storage Diagnostic
 *
 * Analyzes offsite backup storage and geographic redundancy.
 *
 * @since   1.26033.2150
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Offsite Storage Diagnostic
 *
 * Evaluates offsite backup storage configuration.
 *
 * @since 1.26033.2150
 */
class Diagnostic_Offsite_Storage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'offsite-storage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Offsite Storage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes offsite backup storage and geographic redundancy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2150
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'duplicator/duplicator.php'   => 'Duplicator',
			'jetpack/jetpack.php'         => 'Jetpack Backup',
		);

		$active_backup = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup = $name;
				break;
			}
		}

		// Check UpdraftPlus remote storage configuration
		$has_remote_storage = false;
		$remote_storage_type = null;

		if ( $active_backup === 'UpdraftPlus' ) {
			$remote_storage_options = array(
				'updraft_s3'         => 'Amazon S3',
				'updraft_dropbox'    => 'Dropbox',
				'updraft_googledrive' => 'Google Drive',
				'updraft_onedrive'   => 'OneDrive',
				'updraft_ftp'        => 'FTP/SFTP',
			);

			foreach ( $remote_storage_options as $option => $name ) {
				if ( get_option( $option ) ) {
					$has_remote_storage = true;
					$remote_storage_type = $name;
					break;
				}
			}
		}

		// Check backup directory (if backups stored locally only)
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/updraft';
		$has_local_backups = is_dir( $backup_dir );

		// Generate findings if no backup plugin
		if ( ! $active_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected. Offsite storage requires backup solution first.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-storage',
				'meta'         => array(
					'recommendation' => 'Install UpdraftPlus with remote storage',
				),
			);
		}

		// Alert if no remote storage configured
		if ( ! $has_remote_storage && $has_local_backups ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups stored locally only. Server failure would destroy backups - configure offsite storage.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-storage',
				'meta'         => array(
					'has_remote_storage'  => $has_remote_storage,
					'has_local_backups'   => $has_local_backups,
					'active_backup'       => $active_backup,
					'recommendation'      => 'Configure remote storage in UpdraftPlus settings',
					'storage_options'     => array(
						'Amazon S3 (pay per GB)',
						'Google Drive (15GB free)',
						'Dropbox (2GB free)',
						'OneDrive (5GB free)',
						'FTP/SFTP (separate server)',
					),
					'3-2-1_rule'          => '3 copies, 2 media types, 1 offsite',
					'failure_scenarios'   => array(
						'Server hardware failure',
						'Datacenter outage',
						'Ransomware encryption',
						'Accidental deletion',
						'Hosting account suspension',
					),
				),
			);
		}

		// Warning if using same provider for hosting and backups
		if ( $has_remote_storage && $remote_storage_type === 'FTP/SFTP' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'FTP/SFTP storage detected. Ensure FTP server is separate from hosting provider for true redundancy.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-storage',
				'meta'         => array(
					'remote_storage_type' => $remote_storage_type,
					'recommendation'      => 'Verify FTP server is different provider/datacenter',
					'geographic_redundancy' => 'Ideally store backups in different geographic region',
				),
			);
		}

		return null;
	}
}
