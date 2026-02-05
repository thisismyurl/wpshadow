<?php
/**
 * Database Backup Verification Diagnostic
 *
 * Checks if database backups are configured and recent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Backup Verification Diagnostic Class
 *
 * Verifies database backup configuration and recency.
 * Like checking that you have recent copies of important documents.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Database_Backup_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database backups are configured and recent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the database backup verification diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if backup issues detected, null otherwise.
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
		);

		$active_backup_plugin = null;
		foreach ( $backup_plugins as $plugin => $is_active ) {
			if ( $is_active ) {
				$active_backup_plugin = $plugin;
				break;
			}
		}

		// Check UpdraftPlus backup status (most popular).
		if ( class_exists( 'UpdraftPlus' ) ) {
			$last_backup = get_option( 'updraft_last_backup', array() );
			$last_db_backup = $last_backup['db'] ?? 0;

			if ( $last_db_backup > 0 ) {
				$days_since = ( time() - $last_db_backup ) / DAY_IN_SECONDS;

				if ( $days_since > 30 ) {
					return array(
						'id'           => self::$slug . '-outdated',
						'title'        => __( 'Database Backup Outdated', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %d: days since last backup */
							__( 'Your last database backup was %d days ago (like having very old photocopies of important documents). If something goes wrong, you could lose recent data. Run a backup now through UpdraftPlus or set up automatic backups.', 'wpshadow' ),
							(int) $days_since
						),
						'severity'     => 'medium',
						'threat_level' => 60,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/database-backups',
						'context'      => array(
							'last_backup'    => $last_db_backup,
							'days_since'     => $days_since,
							'backup_plugin'  => 'UpdraftPlus',
						),
					);
				}

				if ( $days_since > 7 ) {
					return array(
						'id'           => self::$slug . '-weekly',
						'title'        => __( 'Database Backup Could Be More Frequent', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %d: days since last backup */
							__( 'Your last database backup was %d days ago (like making photocopies weekly instead of daily). For a site that changes often, more frequent backups mean less data loss if something goes wrong. Consider increasing backup frequency in UpdraftPlus.', 'wpshadow' ),
							(int) $days_since
						),
						'severity'     => 'low',
						'threat_level' => 35,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/database-backups',
						'context'      => array(
							'last_backup'    => $last_db_backup,
							'days_since'     => $days_since,
							'backup_plugin'  => 'UpdraftPlus',
						),
					);
				}

				return null; // Recent backup exists.
			}
		}

		// Check BackWPup status.
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$jobs = \BackWPup_Option::get_job_ids();
			if ( ! empty( $jobs ) ) {
				$has_db_backup = false;
				foreach ( $jobs as $job_id ) {
					$job_tasks = \BackWPup_Option::get( $job_id, 'type' );
					if ( in_array( 'DBDUMP', $job_tasks, true ) ) {
						$has_db_backup = true;
						break;
					}
				}

				if ( ! $has_db_backup ) {
					return array(
						'id'           => self::$slug . '-no-db-backup',
						'title'        => __( 'Backup Plugin Not Backing Up Database', 'wpshadow' ),
						'description'  => __( 'BackWPup is active but isn\'t set to back up your database (like having a backup system that only copies some of your files). Make sure at least one backup job includes the database.', 'wpshadow' ),
						'severity'     => 'high',
						'threat_level' => 75,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/database-backups',
						'context'      => array(
							'backup_plugin' => 'BackWPup',
						),
					);
				}

				return null; // BackWPup configured for DB backups.
			}
		}

		// Check for managed hosting backup solutions.
		$managed_hosting = array(
			'WP Engine'   => defined( 'WPE_APIKEY' ),
			'Kinsta'      => defined( 'KINSTAMU_VERSION' ),
			'Flywheel'    => defined( 'FLYWHEEL_CONFIG_DIR' ),
			'Pressable'   => defined( 'IS_PRESSABLE' ),
			'Pagely'      => defined( 'PAGELY_VERSION' ),
		);

		foreach ( $managed_hosting as $host => $detected ) {
			if ( $detected ) {
				return null; // Managed host handles backups.
			}
		}

		// No backup solution found.
		if ( null === $active_backup_plugin ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Database Backups Not Configured', 'wpshadow' ),
				'description'  => __( 'Adding database backups protects your data if something goes wrong (like keeping photocopies of important documents in a safe place). Without backups, a hack, server failure, or accidental deletion could mean permanent data loss. Install a backup plugin like UpdraftPlus (free) or check if your hosting provider offers automated backups.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-backups',
				'context'      => array(),
			);
		}

		// Backup plugin active but can't verify status.
		return array(
			'id'           => self::$slug . '-unknown-status',
			'title'        => __( 'Database Backup Status Unknown', 'wpshadow' ),
			'description'  => sprintf(
				/* translators: %s: backup plugin name */
				__( '%s is active but we can\'t verify when your last database backup ran. Log into your backup plugin to confirm backups are running successfully.', 'wpshadow' ),
				$active_backup_plugin
			),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-backups',
			'context'      => array(
				'backup_plugin' => $active_backup_plugin,
			),
		);
	}
}
