<?php
/**
 * Backup Plugin Installation and Configuration
 *
 * Validates that a backup solution is installed and properly configured.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Plugin_Installation Class
 *
 * Checks for installed backup solutions and their configuration status.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Plugin_Installation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-plugin-installation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Plugin Installation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates backup plugin installation and configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for installed backup plugins
		$installed_backups = self::get_installed_backup_plugins();

		// Pattern 1: No backup plugin installed at all
		if ( empty( $installed_backups ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-plugin-installation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_backup_plugin',
					'message' => __( 'Your site has no backup solution installed or activated', 'wpshadow' ),
					'why_critical' => __( 'Without backups, data loss from hacks, crashes, or mistakes is permanent', 'wpshadow' ),
					'risk_scenarios' => array(
						'Ransomware attack encrypts all data (unrecoverable without backup)',
						'Plugin conflict corrupts database (requires restore)',
						'Accidental mass deletion of posts/pages (only backup helps)',
						'Server hardware failure (backup is sole recovery option)',
						'Failed update bricks entire site (restore to previous version)',
					),
					'business_impact' => __( 'Data loss can cost $5,000-$50,000+ in recovery attempts and lost revenue', 'wpshadow' ),
					'recovery_time_without_backup' => '24-72+ hours (if possible at all)',
					'recovery_time_with_backup' => '15-30 minutes',
					'recommendation' => __( 'Install and configure a backup plugin immediately', 'wpshadow' ),
					'recommended_plugins' => array(
						'UpdraftPlus' => 'Most popular, cloud storage, simple interface',
						'BackWPup' => 'Free, self-hosted backups, scheduled',
						'All in One WP Migration' => 'Easy restore, migration capable',
						'Jetpack Backup' => 'Automatic daily backups, integrated',
						'WP Staging' => 'Backup + staging environment',
					),
					'statistics' => array(
						'60% of sites without backups lose data annually',
						'Average recovery cost: $10,000+',
						'30% of businesses never recover from data loss',
					),
				),
			);
		}

		// Pattern 2: Backup plugin installed but not active
		$active_count = count( array_filter( $installed_backups, function( $plugin ) {
			return $plugin['active'];
		} ) );

		if ( $active_count === 0 && ! empty( $installed_backups ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup plugin installed but not activated', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-plugin-installation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'backup_plugin_inactive',
					'message' => sprintf(
						/* translators: %s: plugin names */
						__( 'Found backup plugins but not active: %s', 'wpshadow' ),
						implode( ', ', wp_list_pluck( $installed_backups, 'name' ) )
					),
					'why_urgent' => __( 'Inactive backup plugins provide zero protection', 'wpshadow' ),
					'fix_steps' => array(
						'1. Go to Plugins in WordPress admin',
						'2. Find your backup plugin(s)',
						'3. Click "Activate"',
						'4. Configure backup schedule',
						'5. Test backup restoration',
					),
					'activation_link' => admin_url( 'plugins.php' ),
					'impact' => __( 'You\'re currently unprotected from data loss', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Backup plugin installed and active but never run
		foreach ( $installed_backups as $backup ) {
			if ( $backup['active'] ) {
				$last_backup = self::get_last_backup_time( $backup['slug'] );

				if ( empty( $last_backup ) ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Backup plugin active but no backups have been created', 'wpshadow' ),
						'severity'     => 'critical',
						'threat_level' => 85,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/backup-plugin-installation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
						'details'      => array(
							'issue' => 'no_backups_created',
							'plugin_name' => $backup['name'],
							'message' => sprintf(
								/* translators: %s: plugin name */
								__( '%s is active but no backup has ever been created', 'wpshadow' ),
								$backup['name']
							),
							'why_urgent' => __( 'Without an initial backup, you have zero recovery options', 'wpshadow' ),
							'next_steps' => array(
								'1. Go to your backup plugin settings',
								'2. Configure backup destination (local, cloud, FTP, etc.)',
								'3. Run manual backup now',
								'4. Verify backup completed successfully',
								'5. Set automatic schedule (daily recommended)',
							),
							'configuration_matters' => __( 'Many users install plugins but forget to actually configure them', 'wpshadow' ),
						),
					);
				}
			}
		}

		// Pattern 4: Backup storage location not configured
		foreach ( $installed_backups as $backup ) {
			if ( $backup['active'] ) {
				$storage_configured = self::is_storage_configured( $backup['slug'] );

				if ( ! $storage_configured ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Backup destination not configured', 'wpshadow' ),
						'severity'     => 'high',
						'threat_level' => 75,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/backup-plugin-installation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
						'details'      => array(
							'issue' => 'backup_storage_unconfigured',
							'plugin_name' => $backup['name'],
							'message' => sprintf(
								/* translators: %s: plugin name */
								__( '%s has no backup destination configured', 'wpshadow' ),
								$backup['name']
							),
							'storage_options' => array(
								'Cloud Storage' => 'Google Drive, Dropbox, Amazon S3 (geographically redundant)',
								'FTP/SFTP' => 'Offsite server (manual management)',
								'Email' => 'Backup sent to email (good for small sites)',
								'Local Server' => 'On same server (risky - single point of failure)',
							),
							'recommendation' => __( 'Use cloud storage for automatic redundancy and disaster recovery', 'wpshadow' ),
							'security_note' => __( 'Ensure backups are encrypted in transit and at rest', 'wpshadow' ),
						),
					);
				}
			}
		}

		// Pattern 5: Multiple backup plugins active (conflicts)
		if ( $active_count > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple backup plugins active simultaneously', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-plugin-installation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'multiple_backup_plugins',
					'message' => sprintf(
						/* translators: %d: number of plugins */
						__( 'Found %d active backup plugins - this can cause conflicts and duplicate backups', 'wpshadow' ),
						$active_count
					),
					'active_plugins' => wp_list_pluck( array_filter( $installed_backups, function( $p ) { return $p['active']; } ), 'name' ),
					'risks' => array(
						'Conflicting backup schedules',
						'Duplicate backup storage costs',
						'Database locks and performance issues',
						'Confusion about which is primary backup',
					),
					'recommendation' => __( 'Deactivate all but one backup plugin. Choose the one that best fits your needs', 'wpshadow' ),
					'best_practice' => __( 'Run only ONE active backup solution to avoid conflicts', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Get list of installed backup plugins.
	 *
	 * @since 0.6093.1200
	 * @return array Array of backup plugins with active status.
	 */
	private static function get_installed_backup_plugins() {
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => array(
				'name' => 'UpdraftPlus',
				'slug' => 'updraftplus',
			),
			'backwpup/backwpup.php' => array(
				'name' => 'BackWPup',
				'slug' => 'backwpup',
			),
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => array(
				'name' => 'All in One WP Migration',
				'slug' => 'all-in-one-wp-migration',
			),
			'jetpack/jetpack.php' => array(
				'name' => 'Jetpack Backup',
				'slug' => 'jetpack',
			),
			'wp-staging/wp-staging.php' => array(
				'name' => 'WP Staging',
				'slug' => 'wp-staging',
			),
			'duplicator/duplicator.php' => array(
				'name' => 'Duplicator',
				'slug' => 'duplicator',
			),
			'snapshot/snapshot.php' => array(
				'name' => 'Snapshot',
				'slug' => 'snapshot',
			),
		);

		$installed = array();

		foreach ( $backup_plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) || file_exists( WP_PLUGIN_DIR . '/' . dirname( $plugin_file ) ) ) {
				$installed[] = array(
					'name'   => $plugin_data['name'],
					'slug'   => $plugin_data['slug'],
					'active' => is_plugin_active( $plugin_file ),
				);
			}
		}

		return $installed;
	}

	/**
	 * Get last backup time for plugin.
	 *
	 * @since 0.6093.1200
	 * @param  string $slug Plugin slug.
	 * @return int|null Timestamp of last backup.
	 */
	private static function get_last_backup_time( $slug ) {
		$last_backup = get_option( $slug . '_last_backup', null );
		return $last_backup;
	}

	/**
	 * Check if backup storage destination is configured.
	 *
	 * @since 0.6093.1200
	 * @param  string $slug Plugin slug.
	 * @return bool True if storage configured.
	 */
	private static function is_storage_configured( $slug ) {
		$storage = get_option( $slug . '_backup_destination', false );
		return ! empty( $storage );
	}
}
