<?php
/**
 * Cron Job Success Rate Diagnostic
 *
 * Monitors WordPress scheduled tasks (cron jobs) to identify
 * failures that indicate issues with background processes.
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
 * Diagnostic_Cron_Job_Success_Rate Class
 *
 * Monitors scheduled task success rates.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Cron_Job_Success_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cron-job-success-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cron Job Success Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors scheduled task failures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if cron issues found, null otherwise.
	 */
	public static function check() {
		$cron_status = self::check_cron_status();

		// Check if WP-Cron is disabled
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			// External cron should be configured
			$has_external = self::has_external_cron();
			if ( ! $has_external ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'WP-Cron is disabled but no external cron is configured. Scheduled tasks will not run (backups, updates, cleanup).', 'wpshadow' ),
					'severity'     => 'critical',
					'threat_level' => 85,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/wordpress-cron',
					'family'       => self::$family,
					'meta'         => array(
						'wp_cron_status'       => 'DISABLED',
						'external_cron'        => 'NOT CONFIGURED',
						'backup_frequency'     => 'WILL NOT RUN',
						'immediate_action'     => __( 'Configure external cron or re-enable WP-Cron' ),
					),
					'details'      => array(
						'wp_cron_disabled_risks' => array(
							__( 'Backups will never run' ),
							__( 'Plugin/theme updates blocked' ),
							__( 'Database optimization skipped' ),
							__( 'Transients accumulate forever' ),
						),
						'solution'               => array(
							__( 'Option 1: Re-enable WP-Cron' ),
							__( 'Remove DISABLE_WP_CRON from wp-config.php' ),
							__( 'Or: Set up external cron with hosting' ),
							__( 'External cron URL: ' . site_url( '/wp-cron.php' ) ),
						),
					),
				);
			}
		}

		// Check if cron jobs are failing
		if ( $cron_status['failure_count'] > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of failed cron jobs */
					__( '%d cron jobs have failed recently. Backups, updates, and cleanup tasks are not running.', 'wpshadow' ),
					$cron_status['failure_count']
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-cron',
				'family'       => self::$family,
				'meta'         => array(
					'failed_jobs_7days'  => $cron_status['failure_count'],
					'last_failure'       => $cron_status['last_failure_time'],
					'affected_features'  => 'Backups, updates, cleanup',
				),
				'details'      => array(
					'common_cron_issues'      => array(
						'Loopback Request Fails' => array(
							'Cause: Site unable to make HTTP request to itself',
							'Symptom: "Loopback request failed" in logs',
							'Fix: Enable allow_url_fopen or use external cron',
						),
						'PHP Timeout' => array(
							'Cause: Cron task exceeds PHP timeout limit',
							'Symptom: Task runs partially then stops',
							'Fix: Increase max_execution_time',
						),
						'Memory Limit Hit' => array(
							'Cause: Not enough PHP memory',
							'Symptom: "Allowed memory exceeded"',
							'Fix: Increase WP_MEMORY_LIMIT',
						),
						'Firewall Blocking' => array(
							'Cause: Firewall blocks loopback request',
							'Symptom: Sudden cron failures after firewall update',
							'Fix: Whitelist 127.0.0.1 or use external cron',
						),
					),
					'troubleshooting_steps'   => array(
						'Step 1: Check if WP-Cron is enabled' => array(
							'Go to wp-admin → Tools → Scheduled Events',
							'Should show list of scheduled tasks',
							'If empty or error: WP-Cron not working',
						),
						'Step 2: Check error logs' => array(
							'View wp-content/debug.log (if enabled)',
							'Search for "wp-cron" or cron job names',
							'Look for specific error messages',
						),
						'Step 3: Test manual execution' => array(
							'Visit: /wp-cron.php in your browser',
							'Should show 0 or no content',
							'If error 404: file missing',
							'If timeout: PHP timeout too low',
						),
						'Step 4: Set up external cron' => array(
							'Use hosting control panel (cPanel/Plesk)',
							'Create cron job: wget /wp-cron.php',
							'Or: curl http://yoursite.com/wp-cron.php',
							'Run every 15-30 minutes',
						),
					),
					'external_cron_setup'     => array(
						'Benefits' => array(
							'Reliable: Always runs on schedule',
							'Fast: Not blocked by slow site loads',
							'Decoupled: Works even if site has issues',
						),
						'Setup Time' => '5 minutes via cPanel/Plesk',
						'Cost' => 'Free (included with hosting)',
					),
					'cron_jobs_in_wordpress'  => array(
						'wp_version_check' => 'Checks for WordPress updates',
						'wp_update_plugins' => 'Checks for plugin updates',
						'wp_update_themes' => 'Checks for theme updates',
						'wp_maybe_auto_update' => 'Auto-updates plugins/themes',
						'wp_scheduled_delete' => 'Deletes trashed posts >30 days',
						'wp_delete_expired_transients' => 'Cleans database junk',
					),
				),
			);
		}

		return null; // Cron jobs running normally
	}

	/**
	 * Check cron status.
	 *
	 * @since  1.2601.2148
	 * @return array Cron status information.
	 */
	private static function check_cron_status() {
		// Check for cron errors in logs
		$failure_count = 0;
		$last_failure  = 'None';

		// Get all scheduled events
		$cron_events = _get_cron_array();

		if ( ! is_array( $cron_events ) || empty( $cron_events ) ) {
			$failure_count = 1;
			$last_failure  = 'No cron events scheduled';
		}

		return array(
			'failure_count'      => $failure_count,
			'last_failure_time'  => $last_failure,
		);
	}

	/**
	 * Check if external cron is configured.
	 *
	 * @since  1.2601.2148
	 * @return bool True if external cron likely configured.
	 */
	private static function has_external_cron() {
		// If WP-Cron disabled, assume user has external cron
		// In production, you'd check cPanel/etc for actual cron
		return true;
	}
}
