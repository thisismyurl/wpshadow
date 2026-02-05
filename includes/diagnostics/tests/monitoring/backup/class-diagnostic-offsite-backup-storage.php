<?php
/**
 * Offsite Backup Storage Diagnostic
 *
 * Checks if backups are stored in a separate location from the server.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1615
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
 * Verifies backups are stored offsite for disaster recovery.
 * Like keeping important documents in a safe deposit box, not at home.
 *
 * @since 1.6035.1615
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
	protected static $description = 'Checks if backups are stored in a separate location from the server';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the offsite backup storage diagnostic check.
	 *
	 * @since  1.6035.1615
	 * @return array|null Finding array if offsite storage issues detected, null otherwise.
	 */
	public static function check() {
		$has_offsite_storage = false;
		$storage_locations = array();

		// Check UpdraftPlus remote storage.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$remote_storage = get_option( 'updraft_service', '' );
			
			if ( ! empty( $remote_storage ) && 'none' !== $remote_storage ) {
				$has_offsite_storage = true;
				$storage_locations[] = self::get_storage_name( $remote_storage );
			}
		}

		// Check BackWPup destinations.
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$jobs = \BackWPup_Option::get_job_ids();
			foreach ( $jobs as $job_id ) {
				$destinations = \BackWPup_Option::get( $job_id, 'destinations' );
				if ( is_array( $destinations ) ) {
					foreach ( $destinations as $dest ) {
						if ( 'FOLDER' !== $dest ) { // FOLDER is local storage.
							$has_offsite_storage = true;
							$storage_locations[] = $dest;
						}
					}
				}
			}
		}

		// Check for managed hosting backups (usually offsite).
		$managed_hosting = array(
			'WP Engine'   => defined( 'WPE_APIKEY' ),
			'Kinsta'      => defined( 'KINSTAMU_VERSION' ),
			'Flywheel'    => defined( 'FLYWHEEL_CONFIG_DIR' ),
			'Pressable'   => defined( 'IS_PRESSABLE' ),
			'Pagely'      => defined( 'PAGELY_VERSION' ),
		);

		foreach ( $managed_hosting as $host => $detected ) {
			if ( $detected ) {
				$has_offsite_storage = true;
				$storage_locations[] = $host . ' Managed Hosting';
				break;
			}
		}

		if ( ! $has_offsite_storage ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Backups Not Stored Offsite', 'wpshadow' ),
				'description'  => __( 'Your backups are only stored on the same server as your site (like keeping all your important documents in one building). If your server fails, gets hacked, or has a catastrophic issue, you lose both your site AND your backups. Set up remote storage like Google Drive, Dropbox, Amazon S3, or another cloud service in your backup plugin settings. This is critical for true disaster recovery.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-backups',
				'context'      => array(),
			);
		}

		// Check if using only one offsite location (recommend redundancy).
		$unique_locations = array_unique( $storage_locations );
		if ( count( $unique_locations ) === 1 ) {
			return array(
				'id'           => self::$slug . '-single-location',
				'title'        => __( 'Backups Stored in Single Offsite Location', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: storage location name */
					__( 'Your backups are stored offsite at %s (which is great!), but only in one location (like keeping your emergency documents in one safe deposit box). For maximum protection, consider adding a second backup destination. This protects you if your primary backup storage has issues or becomes inaccessible.', 'wpshadow' ),
					$unique_locations[0]
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/offsite-backups',
				'context'      => array(
					'locations' => $unique_locations,
				),
			);
		}

		return null; // Offsite storage configured.
	}

	/**
	 * Get user-friendly name for storage service.
	 *
	 * @since  1.6035.1615
	 * @param  string $service_code Service identifier.
	 * @return string User-friendly name.
	 */
	private static function get_storage_name( $service_code ) {
		$names = array(
			's3'          => 'Amazon S3',
			'dropbox'     => 'Dropbox',
			'googledrive' => 'Google Drive',
			'onedrive'    => 'Microsoft OneDrive',
			'ftp'         => 'FTP Server',
			'sftp'        => 'SFTP Server',
			'email'       => 'Email',
			'rackspace'   => 'Rackspace Cloud Files',
			'cloudfiles'  => 'Cloud Files',
			'dreamobjects' => 'DreamObjects',
			'openstack'   => 'OpenStack',
		);

		return $names[ $service_code ] ?? ucfirst( $service_code );
	}
}
