<?php
/**
 * Backup and Recovery Configuration Diagnostic
 *
 * Tests if automated backups are configured for data protection.
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
 * Backup and Recovery Configuration Diagnostic Class
 *
 * Validates that automated backups are configured and stored
 * off-site for disaster recovery.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_And_Recovery_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-and-recovery-configuration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup and Recovery Configuration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if automated backups are configured for data protection';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests backup configuration including automation, off-site
	 * storage, frequency, and restoration testing.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for backup plugins.
		$backup_plugins = array(
			'updraftplus/updraftplus.php'            => 'UpdraftPlus',
			'backwpup/backwpup.php'                  => 'BackWPup',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'duplicator/duplicator.php'              => 'Duplicator',
			'wpvivid-backuprestore/wpvivid-backuprestore.php' => 'WPvivid',
			'blogvault-real-time-backup/blogvault.php' => 'BlogVault',
		);

		$active_backup_plugins = array();
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup_plugins[] = $name;
			}
		}

		$has_backup_plugin = ! empty( $active_backup_plugins );

		// Check UpdraftPlus configuration.
		$updraftplus_scheduled = false;
		$updraftplus_remote = false;
		if ( is_plugin_active( 'updraftplus/updraftplus.php' ) ) {
			$updraft_options = get_option( 'updraft_interval' );
			$updraftplus_scheduled = ! empty( $updraft_options ) && 'manual' !== $updraft_options;

			$updraft_service = get_option( 'updraft_service' );
			$updraftplus_remote = ! empty( $updraft_service ) && ! in_array( $updraft_service, array( 'none', 'email' ), true );
		}

		// Check for scheduled backups in WP Cron.
		$scheduled_backups = wp_get_scheduled_event( 'updraftplus_backup' ) ||
							wp_get_scheduled_event( 'backwpup_cron' );

		// Check backup directory.
		$backup_dir = WP_CONTENT_DIR . '/uploads/backups';
		$backup_dir_exists = is_dir( $backup_dir );

		// Count backup files if directory exists.
		$backup_file_count = 0;
		$total_backup_size = 0;
		$newest_backup_age = null;

		if ( $backup_dir_exists ) {
			$backup_files = glob( $backup_dir . '/*.{zip,sql,gz}', GLOB_BRACE );
			if ( is_array( $backup_files ) ) {
				$backup_file_count = count( $backup_files );

				foreach ( $backup_files as $file ) {
					$total_backup_size += filesize( $file );
				}

				// Find newest backup.
				$newest_file = max( array_map( 'filemtime', $backup_files ) );
				$newest_backup_age = floor( ( time() - $newest_file ) / DAY_IN_SECONDS );
			}
		}

		$backup_size_mb = round( $total_backup_size / ( 1024 * 1024 ), 2 );

		// Check WooCommerce (backups critical for e-commerce).
		$woocommerce_active = is_plugin_active( 'woocommerce/woocommerce.php' );

		// Get database size.
		global $wpdb;
		$db_size = $wpdb->get_var(
			"SELECT SUM(data_length + index_length)
			 FROM information_schema.TABLES
			 WHERE table_schema = '" . DB_NAME . "'"
		);
		$db_size_mb = round( $db_size / ( 1024 * 1024 ), 2 );

		// Check for restoration testing.
		$has_staging = is_plugin_active( 'wp-staging/wp-staging.php' ) ||
					  is_plugin_active( 'wp-stagecoach/wp-stagecoach.php' );

		// Check for issues.
		$issues = array();

		// Issue 1: No backup plugin installed.
		if ( ! $has_backup_plugin ) {
			$issues[] = array(
				'type'        => 'no_backup_plugin',
				'description' => __( 'No backup plugin detected; site data is not protected', 'wpshadow' ),
			);
		}

		// Issue 2: Backup plugin installed but not scheduled.
		if ( $has_backup_plugin && ! $scheduled_backups ) {
			$issues[] = array(
				'type'        => 'no_scheduled_backups',
				'description' => __( 'Backup plugin installed but automated backups not scheduled', 'wpshadow' ),
			);
		}

		// Issue 3: No off-site backup storage.
		if ( $has_backup_plugin && ! $updraftplus_remote ) {
			$issues[] = array(
				'type'        => 'no_offsite_backup',
				'description' => __( 'Backups stored only on same server; vulnerable to server failure', 'wpshadow' ),
			);
		}

		// Issue 4: No recent backups.
		if ( null !== $newest_backup_age && $newest_backup_age > 7 ) {
			$issues[] = array(
				'type'        => 'no_recent_backup',
				'description' => sprintf(
					/* translators: %d: days since last backup */
					__( 'Last backup was %d days ago; should backup at least weekly', 'wpshadow' ),
					$newest_backup_age
				),
			);
		}

		// Issue 5: WooCommerce active but no daily backups.
		if ( $woocommerce_active && ( ! $has_backup_plugin || ! $scheduled_backups ) ) {
			$issues[] = array(
				'type'        => 'ecommerce_no_backup',
				'description' => __( 'WooCommerce active but no automated backups; order data at risk', 'wpshadow' ),
			);
		}

		// Issue 6: No staging environment for restoration testing.
		if ( ! $has_staging && $woocommerce_active ) {
			$issues[] = array(
				'type'        => 'no_staging',
				'description' => __( 'No staging environment; cannot test backup restoration without affecting live site', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Automated backups are not properly configured, which puts site data and business continuity at risk', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-and-recovery-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'has_backup_plugin'       => $has_backup_plugin,
					'active_backup_plugins'   => $active_backup_plugins,
					'updraftplus_scheduled'   => $updraftplus_scheduled,
					'updraftplus_remote'      => $updraftplus_remote,
					'scheduled_backups'       => (bool) $scheduled_backups,
					'backup_dir_exists'       => $backup_dir_exists,
					'backup_file_count'       => $backup_file_count,
					'backup_size_mb'          => $backup_size_mb,
					'newest_backup_age_days'  => $newest_backup_age,
					'database_size_mb'        => $db_size_mb,
					'woocommerce_active'      => $woocommerce_active,
					'has_staging_environment' => $has_staging,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install UpdraftPlus, schedule daily backups, store on Dropbox/Google Drive/S3', 'wpshadow' ),
					'backup_best_practices'   => array(
						'Frequency'           => 'Daily for e-commerce, weekly for content sites',
						'Storage'             => 'Off-site (Dropbox, Google Drive, Amazon S3)',
						'Retention'           => 'Keep 30 daily, 12 monthly backups',
						'Testing'             => 'Test restoration quarterly on staging',
						'Components'          => 'Database, uploads, plugins, themes',
						'Automation'          => 'Scheduled via WP Cron or server cron',
					),
					'storage_recommendations' => array(
						'Dropbox'             => 'Easy setup, 2GB free',
						'Google Drive'        => '15GB free, reliable',
						'Amazon S3'           => 'Pay-as-you-go, enterprise grade',
						'Backblaze B2'        => 'Low cost, S3-compatible',
					),
					'critical_for_woocommerce' => 'Order data, customer information, inventory - all at risk without backups',
					'recovery_time_objective'  => 'Should be able to restore within 4 hours',
				),
			);
		}

		return null;
	}
}
