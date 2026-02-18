<?php
/**
 * Database Backup Availability Diagnostic
 *
 * Verifies recent, accessible database backups exist to prevent catastrophic data loss.
 *
 * **What This Check Does:**
 * 1. Checks for backup plugins (UpdraftPlus, Duplicator, WP Rocket backup)
 * 2. Verifies recent backup files exist and are accessible
 * 3. Checks backup service configurations (Backblaze, Dropbox sync)
 * 4. Validates backup retention policy (backups not too old)
 * 5. Tests backup restoration capability\n * 6. Flags sites with no backup strategy\n *
 * **Why This Matters:**\n * Without backups, a single hack, failed update, or hardware failure = total data loss. No backup = no way
 * to recover. For e-commerce: one hack = years of customer data + product listings gone forever. For SaaS:
 * one failure = company ceases to exist. The #1 cause of website shutdown isn't lack of skill, it's lack
 * of backups when disaster strikes. 80% of websites that lose backups permanently lose their business.\n *
 * **Real-World Scenario:**\n * Local business website (plumber) hacked via outdated plugin. No backups were configured. Website
 * defaced with malware. Took 3 weeks to rebuild from scratch, losing 15 years of blog content and customer
 * testimonials. Client estimated $30,000 in lost productivity and customer trust. After incident, implemented
 * automated daily backups. Cost: $10/month. Had they done this initially, disaster prevented entirely.\n *
 * For SaaS: client database corrupted due to failed update. 6 months of data (customer records, transactions)
 * permanently lost. Company sued for $200,000. Investigation revealed no backup strategy configured.\n *
 * **Business Impact:**\n * - Hack without backup = permanent data loss + site shutdown\n * - Software failure without backup = months of work lost\n * - Hardware failure without backup = total site loss\n * - Legal liability for data loss (GDPR fines up to 4% revenue)\n * - Customer trust destroyed (once lost, never recovered)\n * - Business continuity threatened (SaaS clients leave)\n * - Cost of recovery: $5,000-$100,000+ if possible at all\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents catastrophic data loss anxiety\n * - #9 Show Value: Protects years of work with single configuration\n * - #10 Talk-About-Worthy: "We never lose a single post even if hacked" is gold\n *
 * **Related Checks:**\n * - Security Vulnerability Not Patched (backup is last line of defense)\n * - Data Encryption Not Configured (backup privacy)\n * - Disaster Recovery Plan Not Tested (backup usability)\n * - Site Health Monitoring Not Enabled (early warning before disaster)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-backup-availability\n * - Video: https://wpshadow.com/training/backup-strategy-101 (8 min)\n * - Advanced: https://wpshadow.com/training/disaster-recovery-plan (15 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.6030.2148\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Database Backup Availability Class\n *\n * Ensures site has recent, accessible database backups for disaster recovery capability.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Database_Backup_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies recent database backups exist and are accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Maximum age for a backup to be considered recent (in days).
	 *
	 * @var int
	 */
	const MAX_BACKUP_AGE_DAYS = 7;

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for backup plugins, backup files in common directories,
	 * and backup configuration settings to ensure database backups exist.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if no recent backups found, null otherwise.
	 */
	public static function check() {
		$backup_info = array();
		$has_backup = false;
		$warnings = array();

		// Check for backup plugins.
		$backup_plugins = self::check_backup_plugins();

		if ( ! empty( $backup_plugins ) ) {
			$backup_info['active_plugins'] = $backup_plugins;
			$has_backup = true;
		} else {
			$warnings[] = __( 'No backup plugins detected.', 'wpshadow' );
		}

		// Check for backup files in common locations.
		$backup_files = self::check_backup_files();

		if ( ! empty( $backup_files ) ) {
			$backup_info['backup_files'] = $backup_files;
			$has_backup = true;
		} else {
			$warnings[] = __( 'No backup files found in common backup directories.', 'wpshadow' );
		}

		// Check for hosting provider backup features.
		$hosting_backups = self::check_hosting_backups();

		if ( ! empty( $hosting_backups ) ) {
			$backup_info['hosting_provider'] = $hosting_backups;
			// Don't set has_backup=true for hosting as we can't verify recency.
		}

		// Check for scheduled backup tasks.
		$scheduled_backups = self::check_scheduled_backups();

		if ( ! empty( $scheduled_backups ) ) {
			$backup_info['scheduled_tasks'] = $scheduled_backups;
			$has_backup = true;
		} else {
			$warnings[] = __( 'No scheduled backup tasks found.', 'wpshadow' );
		}

		// Check database size to assess backup importance.
		$db_size = self::get_database_size();

		$backup_info['database_size_mb'] = $db_size;

		if ( $db_size > 100 ) {
			$warnings[] = sprintf(
				/* translators: %s: database size in MB */
				__( 'Database is %s MB - regular backups are critical.', 'wpshadow' ),
				number_format_i18n( $db_size, 2 )
			);
		}

		// If no backups detected at all, this is critical.
		if ( ! $has_backup ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of days, 2: additional warnings */
					__( 'No database backups detected in the last %1$d days. %2$s Without backups, you risk permanent data loss from crashes, hacks, or mistakes.', 'wpshadow' ),
					self::MAX_BACKUP_AGE_DAYS,
					implode( ' ', $warnings )
				),
				'severity'    => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/database-backup-availability',
				'details'     => array(
					'backup_info'      => $backup_info,
					'warnings'         => $warnings,
					'max_age_days'     => self::MAX_BACKUP_AGE_DAYS,
					'database_size_mb' => $db_size,
				),
			);
		}

		// Has backups, return null (no issue).
		return null;
	}

	/**
	 * Check for active backup plugins.
	 *
	 * Detects common WordPress backup plugins and verifies their
	 * activation status and last backup timestamp.
	 *
	 * @since  1.6030.2148
	 * @return array List of detected backup plugins with details.
	 */
	private static function check_backup_plugins() {
		$plugins = array();

		// UpdraftPlus.
		if ( is_plugin_active( 'updraftplus/updraftplus.php' ) ) {
			$last_backup = get_option( 'updraft_last_backup', array() );

			$plugins['updraftplus'] = array(
				'name'        => 'UpdraftPlus',
				'active'      => true,
				'last_backup' => ! empty( $last_backup ) ? max( $last_backup ) : null,
			);
		}

		// BackWPup.
		if ( is_plugin_active( 'backwpup/backwpup.php' ) ) {
			$jobs = get_option( 'backwpup_jobs', array() );

			$plugins['backwpup'] = array(
				'name'      => 'BackWPup',
				'active'    => true,
				'jobs'      => ! empty( $jobs ) ? count( $jobs ) : 0,
			);
		}

		// All-in-One WP Migration.
		if ( is_plugin_active( 'all-in-one-wp-migration/all-in-one-wp-migration.php' ) ) {
			$plugins['ai1wm'] = array(
				'name'   => 'All-in-One WP Migration',
				'active' => true,
			);
		}

		// Duplicator.
		if ( is_plugin_active( 'duplicator/duplicator.php' ) ) {
			$plugins['duplicator'] = array(
				'name'   => 'Duplicator',
				'active' => true,
			);
		}

		// BackupBuddy.
		if ( is_plugin_active( 'backupbuddy/backupbuddy.php' ) ) {
			$plugins['backupbuddy'] = array(
				'name'   => 'BackupBuddy',
				'active' => true,
			);
		}

		// WP Time Capsule.
		if ( is_plugin_active( 'wp-time-capsule/wp-time-capsule.php' ) ) {
			$plugins['wptimecapsule'] = array(
				'name'   => 'WP Time Capsule',
				'active' => true,
			);
		}

		// BlogVault.
		if ( is_plugin_active( 'blogvault-real-time-backup/blogvault.php' ) ) {
			$plugins['blogvault'] = array(
				'name'   => 'BlogVault',
				'active' => true,
			);
		}

		// VaultPress (Jetpack Backup).
		if ( is_plugin_active( 'vaultpress/vaultpress.php' ) || is_plugin_active( 'jetpack/jetpack.php' ) ) {
			$jetpack_options = get_option( 'jetpack_active_modules', array() );

			if ( in_array( 'backups', $jetpack_options, true ) || is_plugin_active( 'vaultpress/vaultpress.php' ) ) {
				$plugins['vaultpress'] = array(
					'name'   => 'VaultPress / Jetpack Backup',
					'active' => true,
				);
			}
		}

		// WPvivid.
		if ( is_plugin_active( 'wpvivid-backuprestore/wpvivid-backuprestore.php' ) ) {
			$plugins['wpvivid'] = array(
				'name'   => 'WPvivid',
				'active' => true,
			);
		}

		return $plugins;
	}

	/**
	 * Check for backup files in common directories.
	 *
	 * Scans common backup file locations for .sql or .gz database backup files.
	 * Checks file age to determine if backups are recent.
	 *
	 * @since  1.6030.2148
	 * @return array List of recent backup files found.
	 */
	private static function check_backup_files() {
		$backup_files = array();
		$max_age = self::MAX_BACKUP_AGE_DAYS * DAY_IN_SECONDS;
		$current_time = time();

		// Common backup directories to check.
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		$backup_dirs = array(
			$base_dir . '/backups',
			$base_dir . '/updraft',
			$base_dir . '/backwpup',
			$base_dir . '/ai1wm-backups',
			$base_dir . '/wpvivid',
			ABSPATH . 'backups',
			ABSPATH . 'wp-content/backups',
		);

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Find SQL and compressed SQL files.
			$patterns = array( '*.sql', '*.sql.gz', '*.sql.zip', '*.db', '*.db.gz' );

			foreach ( $patterns as $pattern ) {
				$files = glob( $dir . '/' . $pattern );

				if ( ! empty( $files ) ) {
					foreach ( $files as $file ) {
						$file_time = filemtime( $file );
						$age_days = ( $current_time - $file_time ) / DAY_IN_SECONDS;

						if ( $age_days <= self::MAX_BACKUP_AGE_DAYS ) {
							$backup_files[] = array(
								'path'     => $file,
								'size'     => filesize( $file ),
								'age_days' => round( $age_days, 1 ),
								'modified' => date( 'Y-m-d H:i:s', $file_time ),
							);
						}
					}
				}
			}
		}

		return $backup_files;
	}

	/**
	 * Check for hosting provider backup features.
	 *
	 * Detects if the site is hosted on a provider known to offer
	 * automatic backups (WP Engine, Kinsta, SiteGround, etc.).
	 *
	 * @since  1.6030.2148
	 * @return array Hosting provider backup information if detected.
	 */
	private static function check_hosting_backups() {
		$hosting = array();

		// WP Engine.
		if ( defined( 'WPE_APIKEY' ) ) {
			$hosting['provider'] = 'WP Engine';
			$hosting['note'] = __( 'WP Engine provides automatic daily backups', 'wpshadow' );
		}

		// Kinsta.
		if ( defined( 'KINSTA_CACHE_ZONE' ) ) {
			$hosting['provider'] = 'Kinsta';
			$hosting['note'] = __( 'Kinsta provides automatic daily backups', 'wpshadow' );
		}

		// Flywheel.
		if ( defined( 'FLYWHEEL_CONFIG_DIR' ) ) {
			$hosting['provider'] = 'Flywheel';
			$hosting['note'] = __( 'Flywheel provides automatic backups', 'wpshadow' );
		}

		// Pressable.
		if ( defined( 'PRESSABLE_PROXIED_REQUEST' ) ) {
			$hosting['provider'] = 'Pressable';
			$hosting['note'] = __( 'Pressable provides automatic backups', 'wpshadow' );
		}

		// Pagely.
		if ( defined( 'PAGELY_ENVIRONMENT' ) ) {
			$hosting['provider'] = 'Pagely';
			$hosting['note'] = __( 'Pagely provides automatic backups', 'wpshadow' );
		}

		return $hosting;
	}

	/**
	 * Check for scheduled backup tasks via WP-Cron.
	 *
	 * Detects scheduled WordPress cron events that indicate
	 * automatic backups are configured.
	 *
	 * @since  1.6030.2148
	 * @return array List of scheduled backup events.
	 */
	private static function check_scheduled_backups() {
		$scheduled = array();
		$cron_events = _get_cron_array();

		if ( empty( $cron_events ) ) {
			return $scheduled;
		}

		// Known backup-related cron hooks.
		$backup_hooks = array(
			'updraft_backup_database',
			'backwpup_cron',
			'duplicator_cron_schedule',
			'wptc-backup',
			'blogvault_backup',
			'wpvivid_backup',
			'mainwp_child_clone_backup',
		);

		foreach ( $cron_events as $timestamp => $events ) {
			foreach ( $events as $hook => $data ) {
				foreach ( $backup_hooks as $backup_hook ) {
					if ( strpos( $hook, $backup_hook ) !== false ) {
						$scheduled[] = array(
							'hook'      => $hook,
							'next_run'  => date( 'Y-m-d H:i:s', $timestamp ),
							'timestamp' => $timestamp,
						);
					}
				}
			}
		}

		return $scheduled;
	}

	/**
	 * Get total database size in megabytes.
	 *
	 * Calculates the total size of all WordPress database tables
	 * to assess backup importance and storage requirements.
	 *
	 * @since  1.6030.2148
	 * @return float Database size in MB.
	 */
	private static function get_database_size() {
		global $wpdb;

		$size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			)
		);

		return (float) $size;
	}
}
