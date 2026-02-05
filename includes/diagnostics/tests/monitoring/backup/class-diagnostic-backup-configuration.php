<?php
/**
 * Backup Configuration Diagnostic
 *
 * Checks if any backup solution is configured.
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
 * Backup Configuration Diagnostic Class
 *
 * Verifies a backup system is in place.
 * Like checking that you have insurance for your valuables.
 *
 * @since 1.6035.1615
 */
class Diagnostic_Backup_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if any backup solution is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the backup configuration diagnostic check.
	 *
	 * @since  1.6035.1615
	 * @return array|null Finding array if backup configuration issues detected, null otherwise.
	 */
	public static function check() {
		// Check for popular backup plugins.
		$backup_plugins = array(
			'UpdraftPlus'              => class_exists( 'UpdraftPlus' ),
			'BackWPup'                 => class_exists( 'BackWPup' ),
			'All-in-One WP Migration'  => class_exists( 'All_in_One_WP_Migration' ),
			'Duplicator'               => class_exists( 'Duplicator' ),
			'BackupBuddy'              => class_exists( 'backupbuddy_core' ),
			'VaultPress'               => class_exists( 'VaultPress' ),
			'BlogVault'                => defined( 'BLOGVAULT_VERSION' ),
			'WPvivid'                  => class_exists( 'WPvivid' ),
			'VersionPress'             => defined( 'VERSIONPRESS_VERSION' ),
			'BackUpWordPress'          => class_exists( 'HM_Backup' ),
		);

		$active_backup_plugins = array();
		foreach ( $backup_plugins as $plugin => $is_active ) {
			if ( $is_active ) {
				$active_backup_plugins[] = $plugin;
			}
		}

		// Check for managed hosting with built-in backups.
		$managed_hosting = array(
			'WP Engine'   => defined( 'WPE_APIKEY' ),
			'Kinsta'      => defined( 'KINSTAMU_VERSION' ),
			'Flywheel'    => defined( 'FLYWHEEL_CONFIG_DIR' ),
			'Pressable'   => defined( 'IS_PRESSABLE' ),
			'Pagely'      => defined( 'PAGELY_VERSION' ),
			'SiteGround'  => defined( 'WP_SITEGROUND_VERSION' ),
		);

		$has_managed_hosting = false;
		$hosting_provider = '';
		foreach ( $managed_hosting as $host => $detected ) {
			if ( $detected ) {
				$has_managed_hosting = true;
				$hosting_provider = $host;
				break;
			}
		}

		// If managed hosting provides backups, we're good.
		if ( $has_managed_hosting ) {
			return null; // Managed host handles backups.
		}

		// No backup solution found.
		if ( empty( $active_backup_plugins ) ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Backups Not Configured', 'wpshadow' ),
				'description'  => __( 'Adding a backup system protects your site if something goes wrong (like having insurance for your home). Without backups, a hack, server failure, bad update, or accidental deletion means permanent data loss. Install a backup plugin like UpdraftPlus (free) or check if your hosting provider offers automatic backups in your control panel.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-setup',
				'context'      => array(),
			);
		}

		// Multiple backup plugins (potential conflict).
		if ( count( $active_backup_plugins ) > 1 ) {
			return array(
				'id'           => self::$slug . '-multiple-plugins',
				'title'        => __( 'Multiple Backup Plugins Active', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of backup plugins */
					__( 'You have multiple backup plugins active: %s (like having two different insurance companies for the same property—confusing and potentially conflicting). This can cause duplicate backups, wasted storage, and conflicts. Choose one backup solution and deactivate the others.', 'wpshadow' ),
					implode( ', ', $active_backup_plugins )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-setup',
				'context'      => array(
					'plugins' => $active_backup_plugins,
				),
			);
		}

		return null; // Backup plugin active and singular.
	}
}
