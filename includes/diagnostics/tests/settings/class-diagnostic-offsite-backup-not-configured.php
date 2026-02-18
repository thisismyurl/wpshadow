<?php
/**
 * Offsite Backup Not Configured Diagnostic
 *
 * Checks if backups are stored offsite/remotely.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Offsite_Backup_Not_Configured Class
 *
 * Detects when backups are only stored locally (same server).
 * Local-only backups are vulnerable to server failures, fires, and ransomware.
 *
 * @since 1.2033.0000
 */
class Diagnostic_Offsite_Backup_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'offsite-backup-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Offsite Backup Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are stored offsite';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Remote backup plugins (BackWPup Pro, UpdraftPlus Premium)
	 * - Cloud storage configuration (S3, Google Drive, Dropbox)
	 * - Vault configuration
	 *
	 * @since  1.2033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Vault is active (has offsite backups).
		if ( Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
			return null;
		}

		// Check for common remote backup configurations.
		$has_offsite = false;

		// Check for BackWPup Pro with cloud destination.
		if ( is_plugin_active( 'backwpup-pro/backwpup-pro.php' ) ) {
			$jobs = get_option( 'backwpup_jobs', array() );
			foreach ( $jobs as $job ) {
				$destinations = isset( $job['destinations'] ) ? $job['destinations'] : array();
				if ( is_array( $destinations ) ) {
					// Check for cloud destinations (not just local folder).
					$cloud_destinations = array_intersect( $destinations, array( 's3', 'dropbox', 'gdrive', 'azure', 'ftp', 'sugarsync' ) );
					if ( ! empty( $cloud_destinations ) ) {
						$has_offsite = true;
						break;
					}
				}
			}
		}

		// Check for UpdraftPlus with remote storage.
		if ( is_plugin_active( 'updraftplus/updraftplus.php' ) ) {
			$remote_storage = get_option( 'updraft_service', '' );
			if ( ! empty( $remote_storage ) && 'none' !== $remote_storage ) {
				$has_offsite = true;
			}
		}

		// Check for Jetpack VaultPress Backup.
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
				if ( \Jetpack::is_module_active( 'vaultpress' ) ) {
					$has_offsite = true;
				}
			}
		}

		if ( ! $has_offsite ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your backups are only stored on the same server as your website. If the server fails (hardware crash, fire, flood, ransomware), you lose both your site AND your backups. This is the #1 cause of permanent data loss. Industry best practice: Follow the 3-2-1 rule (3 copies, 2 different media, 1 offsite).', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-backup-configuration',
			);

			// Add upgrade path to Vault.
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'vault',
				'cloud-offload',
				'https://wpshadow.com/kb/manual-offsite-backup-setup'
			);

			return $finding;
		}

		return null;
	}
}
