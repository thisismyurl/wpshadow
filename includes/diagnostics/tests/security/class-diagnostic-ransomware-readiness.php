<?php
/**
 * Ransomware Readiness
 *
 * Verifies backup separation and immutability features are in place
 * to protect against ransomware attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6029.1106
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ransomware Readiness Diagnostic Class
 *
 * Checks for backup isolation and immutability to prevent
 * ransomware from encrypting backups.
 *
 * @since 1.6029.1106
 */
class Diagnostic_Ransomware_Readiness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ransomware-readiness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Ransomware Readiness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies backup separation and immutability (ransomware protection)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1106
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_ransomware_readiness_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$readiness = self::assess_ransomware_protection();

		if ( $readiness['protected'] ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Backup system lacks ransomware protection features. Backups may be vulnerable to encryption attacks.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ransomware-readiness',
			'meta'         => array(
				'has_offsite_backup' => $readiness['has_offsite'],
				'has_immutable_backup' => $readiness['has_immutable'],
				'backup_plugin' => $readiness['backup_plugin'],
			),
			'details'      => array(
				__( 'Backups should be stored offsite or in isolated storage', 'wpshadow' ),
				__( 'Immutable backups cannot be modified or encrypted by ransomware', 'wpshadow' ),
				__( 'Local-only backups are vulnerable to ransomware encryption', 'wpshadow' ),
			),
			'recommendation' => __( 'Configure offsite backups to cloud storage with immutability features (AWS S3 Object Lock, Backblaze B2, etc.).', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Assess ransomware protection.
	 *
	 * @since  1.6029.1106
	 * @return array Protection assessment.
	 */
	private static function assess_ransomware_protection() {
		// Check for backup plugins with offsite/immutable features.
		$protected_plugins = array(
			'updraftplus/updraftplus.php' => array(
				'offsite'   => self::check_updraftplus_offsite(),
				'immutable' => false, // UpdraftPlus doesn't have native immutability.
			),
			'backwpup/backwpup.php' => array(
				'offsite'   => self::check_backwpup_offsite(),
				'immutable' => false,
			),
			'backup/backup.php' => array(
				'offsite'   => true, // Jetpack Backup is always offsite.
				'immutable' => true, // Jetpack has immutability.
			),
		);

		$active_plugin   = null;
		$has_offsite     = false;
		$has_immutable   = false;

		foreach ( $protected_plugins as $plugin => $features ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $plugin;
				$has_offsite   = $features['offsite'];
				$has_immutable = $features['immutable'];
				break;
			}
		}

		return array(
			'protected'      => $has_offsite && $has_immutable,
			'has_offsite'    => $has_offsite,
			'has_immutable'  => $has_immutable,
			'backup_plugin'  => $active_plugin,
		);
	}

	/**
	 * Check UpdraftPlus offsite configuration.
	 *
	 * @since  1.6029.1106
	 * @return bool Whether offsite backup is configured.
	 */
	private static function check_updraftplus_offsite() {
		$settings = get_option( 'updraft_service', '' );
		$offsite_services = array( 's3', 'dropbox', 'googledrive', 'azure', 'backblaze' );

		foreach ( $offsite_services as $service ) {
			if ( false !== strpos( $settings, $service ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check BackWPup offsite configuration.
	 *
	 * @since  1.6029.1106
	 * @return bool Whether offsite backup is configured.
	 */
	private static function check_backwpup_offsite() {
		$jobs = get_option( 'backwpup_jobs', array() );

		foreach ( $jobs as $job ) {
			if ( isset( $job['destinations'] ) && is_array( $job['destinations'] ) ) {
				$offsite = array( 's3', 'dropbox', 'sugarsync', 'ftp', 'rsc' );
				foreach ( $offsite as $dest ) {
					if ( in_array( $dest, $job['destinations'], true ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}
