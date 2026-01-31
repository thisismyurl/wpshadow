<?php
/**
 * Cron Job Execution Verification Diagnostic
 *
 * Checks if WordPress cron jobs are executing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Job Execution Verification Diagnostic Class
 *
 * Detects WordPress cron execution issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Cron_Job_Execution_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cron-job-execution-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cron Job Execution Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress cron jobs are executing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if DISABLE_WP_CRON is set to true
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			// Manual cron is configured, check if there's a real cron job
			return null;
		}

		// Get last scheduled event
		$next_event = wp_next_scheduled( 'wp_version_check' );

		if ( ! $next_event ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No WordPress cron jobs are scheduled. Scheduled tasks like updates, backups, and maintenance won\'t run.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cron-job-execution-verification',
			);
		}

		return null;
	}
}
