<?php
/**
 * Cron Job Performance Treatment
 *
 * Checks for long-running or stuck cron jobs.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2072
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Job Performance Treatment Class
 *
 * Analyzes WP-Cron for performance issues. Excessive or stuck
 * cron jobs can cause page load delays.
 *
 * @since 1.6033.2072
 */
class Treatment_Cron_Job_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cron-job-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cron Job Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for cron job performance issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes cron events for excessive jobs or missed schedules.
	 * WP-Cron runs on page load, impacting performance.
	 *
	 * @since  1.6033.2072
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cron_Job_Performance' );
	}
}
