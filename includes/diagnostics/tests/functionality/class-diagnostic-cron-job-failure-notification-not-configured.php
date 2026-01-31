<?php
/**
 * Cron Job Failure Notification Not Configured Diagnostic
 *
 * Checks if cron failures are notified.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Job Failure Notification Not Configured Diagnostic Class
 *
 * Detects missing cron failure notifications.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Cron_Job_Failure_Notification_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cron-job-failure-notification-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cron Job Failure Notification Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cron failures are notified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if cron monitoring is configured
		if ( ! has_action( 'wpshadow_cron_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cron job failure notifications are not configured. Set up monitoring to alert you when scheduled tasks fail.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cron-job-failure-notification-not-configured',
			);
		}

		return null;
	}
}
