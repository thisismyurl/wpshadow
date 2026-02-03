<?php
/**
 * Disaster Recovery Testing and Restoration Capability
 *
 * Validates that backups can be successfully restored.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Disaster_Recovery_Testing Class
 *
 * Checks that backups have been tested and can be restored.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Disaster_Recovery_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disaster-recovery-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates backup restoration capability and testing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for active backup plugin
		$backup_plugin = self::get_active_backup_plugin();

		if ( ! $backup_plugin ) {
			return null; // No backup plugin, skip check
		}

		// Pattern 1: Backups exist but have never been tested
		$backups_exist = self::has_backups( $backup_plugin );

		if ( $backups_exist ) {
			$last_test = self::get_last_backup_test( $backup_plugin );

			if ( empty( $last_test ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Backups exist but have never been tested', 'wpshadow' ),
					'severity'     => 'critical',
					'threat_level' => 90,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
					'details'      => array(
						'issue' => 'backups_never_tested',
						'message' => __( 'You have backups but have never verified they can be restored', 'wpshadow' ),
						'critical_reality' => __( 'Untested backups fail 50% of the time when you actually need them', 'wpshadow' ),
						'statistics' => array(
							'54% of companies discover backup failures during actual disasters',
							'3 out of 5 backup restoration attempts fail',
							'Average unplanned downtime costs $5,600 per minute',
						),
						'why_testing_essential' => array(
							'Backups corrupt during creation (detected in test)',
							'Storage permissions wrong (prevents restore)',
							'Database structure changed (backup incompatible)',
							'Plugin versions incompatible (restore fails)',
						),
						'recommended_test_schedule' => array(
							'New site' => 'After first backup (before production)',
							'Established site' => 'Monthly minimum',
							'Mission-critical' => 'Weekly',
							'Enterprise' => 'Daily',
						),
						'testing_steps' => array(
							'1. In backup plugin, find "Restore" or "Test" option',
							'2. Click to view recent backups',
							'3. Start test restore on oldest backup',
							'4. Verify test completes without errors',
							'5. Check that test results show full database restored',
							'6. Document successful test date/time',
							'7. Schedule monthly test reminder',
						),
						'recovery_formula' => __( 'RTO (Recovery Time Objective): How long to restore? Can you afford this downtime?', 'wpshadow' ),
						'recommendation' => __( 'Perform a test restore on your latest backup today', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 2: Last backup test older than 90 days
		$last_test = self::get_last_backup_test( $backup_plugin );

		if ( $last_test && ( time() - $last_test ) > ( 90 * DAY_IN_SECONDS ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups not tested in over 90 days', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
				'details'      => array(
					'issue' => 'backup_test_stale',
					'last_tested' => date_i18n( get_option( 'date_format' ), $last_test ),
					'days_since_test' => ceil( ( time() - $last_test ) / DAY_IN_SECONDS ),
					'message' => sprintf(
						/* translators: %d: days */
						__( 'Backups last tested %d days ago', 'wpshadow' ),
						ceil( ( time() - $last_test ) / DAY_IN_SECONDS )
					),
					'risk' => __( 'Site configuration has changed since last test; backup might be incompatible now', 'wpshadow' ),
					'what_changes' => array(
						'Plugin updates that changed database schema',
						'Theme updates that affect content',
						'WordPress core updates',
						'PHP version changes',
						'MySQL/MariaDB version changes',
					),
					'broken_backup_scenarios' => array(
						'You update WordPress, then disaster strikes',
						'Backup from before update, won\'t restore under new version',
						'Result: Restore fails, site offline until manual recovery',
					),
					'best_practice' => __( 'Test backups at least monthly, ideally after major updates', 'wpshadow' ),
					'recommendation' => __( 'Run a backup test immediately to verify current backups work', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Restoration documentation not created
		$has_recovery_plan = self::has_recovery_plan( $backup_plugin );

		if ( ! $has_recovery_plan ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No disaster recovery plan documentation created', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
				'details'      => array(
					'issue' => 'no_recovery_plan',
					'message' => __( 'No documented recovery procedure or runbook exists', 'wpshadow' ),
					'why_matters' => __( 'During crisis, people panic; documented steps ensure proper recovery', 'wpshadow' ),
					'crisis_scenario' => __( 'Site is down, CEO demands immediate restore, you can\'t remember the steps', 'wpshadow' ),
					'recovery_plan_checklist' => array(
						'Where are backups stored? (cloud service, FTP server, etc.)',
						'How to access backup plugin? (admin login, credentials)',
						'Step-by-step restore procedure (written out)',
						'Estimated restoration time',
						'Who to contact if restore fails',
						'Post-restore verification steps',
						'Who is responsible for testing backups',
						'Backup test schedule/calendar',
					),
					'documented_recovery_benefits' => array(
						'Anyone can restore (not just original developer)',
						'Faster restoration (don\'t need to remember)',
						'Consistent process reduces errors',
						'Compliance requirement (audit trails)',
					),
					'recommended_format' => 'Create document with:
- Backup location and access credentials
- Step-by-step restore procedure
- Screenshots of key steps
- Troubleshooting section
- Test date log',
					'recommendation' => __( 'Create documented recovery procedure immediately', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Staging environment not available for testing
		$has_staging = self::has_staging_environment();

		if ( ! $has_staging ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No staging environment for safe backup testing', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
				'details'      => array(
					'issue' => 'no_staging_environment',
					'message' => __( 'No staging/test environment to safely test restore procedures', 'wpshadow' ),
					'why_important' => __( 'Testing restore on live site is risky; staging allows safe testing', 'wpshadow' ),
					'staging_benefits' => array(
						'Test restore without affecting live users',
						'Verify backup completeness before deletion',
						'Test update compatibility',
						'Perform load testing',
						'Troubleshoot issues in isolation',
					),
					'staging_setup_options' => array(
						'WP Staging Pro' => 'Creates temporary clone for testing',
						'All in One WP Migration' => 'Backup then restore to staging',
						'Duplicator' => 'Full site clone for staging',
						'Manual staging server' => 'Secondary WordPress installation',
					),
					'recommended_setup' => 'Create staging environment in subdirectory (staging.yoursite.com)',
					'testing_workflow' => array(
						'1. Create staging copy of live site',
						'2. Restore backup to staging',
						'3. Verify content and functionality',
						'4. Document any issues',
						'5. Keep staging up-to-date with production',
					),
					'recommendation' => __( 'Set up staging environment for safe backup testing', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Restore documentation is outdated
		$recovery_plan_age = self::get_recovery_plan_age( $backup_plugin );

		if ( $recovery_plan_age && $recovery_plan_age > ( 180 * DAY_IN_SECONDS ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Recovery plan documentation is outdated', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
				'details'      => array(
					'issue' => 'recovery_plan_outdated',
					'last_updated' => date_i18n( get_option( 'date_format' ), $recovery_plan_age ),
					'days_old' => ceil( ( time() - $recovery_plan_age ) / DAY_IN_SECONDS ),
					'message' => sprintf(
						/* translators: %d: days */
						__( 'Recovery plan not updated in %d days', 'wpshadow' ),
						ceil( ( time() - $recovery_plan_age ) / DAY_IN_SECONDS )
					),
					'what_changes' => __( 'Server, hosting provider, plugin versions - procedure might be wrong now', 'wpshadow' ),
					'risks' => array(
						'Documented steps might not work anymore',
						'Passwords may have changed',
						'Plugin interface changed',
						'Hosting control panel updated',
					),
					'update_frequency' => 'Every 6 months or after major changes',
					'recommended_review_items' => array(
						'Backup location still accessible?',
						'Login credentials still valid?',
						'Backup plugin interface changed?',
						'Hosting provider changed?',
						'New plugins/tools available?',
					),
					'recommendation' => __( 'Review and update recovery plan documentation', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: No restore time monitoring in place
		$restore_time_documented = self::is_restore_time_documented( $backup_plugin );

		if ( ! $restore_time_documented ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Recovery time objective (RTO) not documented', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disaster-recovery-testing',
				'details'      => array(
					'issue' => 'rto_not_defined',
					'message' => __( 'No documented Recovery Time Objective (RTO) or Service Level Agreement', 'wpshadow' ),
					'why_important' => __( 'Knowing acceptable downtime helps plan recovery resources', 'wpshadow' ),
					'rto_examples' => array(
						'E-commerce' => 'RTO: 1 hour (loses $1,000+/hour)',
						'SaaS platform' => 'RTO: 30 minutes (critical for operations)',
						'Blog' => 'RTO: 24 hours (acceptable temporary outage)',
						'Corporate site' => 'RTO: 4 hours (business impact)',
					),
					'business_continuity' => __( 'RTO determines backup frequency and recovery testing requirements', 'wpshadow' ),
					'rto_planning' => array(
						'Calculate maximum acceptable downtime cost',
						'Estimate actual restoration time from backup',
						'Define recovery priorities (which data first?)',
						'Create escalation procedures',
						'Assign recovery team roles',
					),
					'recommendation' => __( 'Document your RTO and create recovery procedures based on it', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Get active backup plugin slug.
	 *
	 * @since  1.2601.2148
	 * @return string Plugin slug or empty.
	 */
	private static function get_active_backup_plugin() {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'jetpack/jetpack.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return basename( dirname( $plugin ) );
			}
		}

		return '';
	}

	/**
	 * Check if backups exist.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin slug.
	 * @return bool True if backups exist.
	 */
	private static function has_backups( $plugin ) {
		return (bool) get_option( $plugin . '_backup_count', false );
	}

	/**
	 * Get last backup test timestamp.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin slug.
	 * @return int Timestamp or 0.
	 */
	private static function get_last_backup_test( $plugin ) {
		return absint( get_option( $plugin . '_last_test', 0 ) );
	}

	/**
	 * Check if recovery plan exists.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin slug.
	 * @return bool True if plan exists.
	 */
	private static function has_recovery_plan( $plugin ) {
		return (bool) get_option( $plugin . '_recovery_plan_documented', false );
	}

	/**
	 * Check if staging environment exists.
	 *
	 * @since  1.2601.2148
	 * @return bool True if staging exists.
	 */
	private static function has_staging_environment() {
		return is_plugin_active( 'wp-staging/wp-staging.php' ) || 
			   is_plugin_active( 'all-in-one-wp-migration/all-in-one-wp-migration.php' );
	}

	/**
	 * Get recovery plan age.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin slug.
	 * @return int Timestamp.
	 */
	private static function get_recovery_plan_age( $plugin ) {
		return absint( get_option( $plugin . '_recovery_plan_updated', 0 ) );
	}

	/**
	 * Check if restore time is documented.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin slug.
	 * @return bool True if documented.
	 */
	private static function is_restore_time_documented( $plugin ) {
		return (bool) get_option( $plugin . '_rto_minutes', false );
	}
}
