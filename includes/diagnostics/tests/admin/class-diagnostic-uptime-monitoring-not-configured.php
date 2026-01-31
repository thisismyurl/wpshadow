<?php
/**
 * Uptime Monitoring Not Configured Diagnostic
 *
 * Checks if uptime monitoring is configured.
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
 * Uptime Monitoring Not Configured Diagnostic Class
 *
 * Detects missing uptime monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Uptime_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uptime-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uptime Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if uptime monitoring is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if uptime monitoring service is integrated
		if ( ! has_option( 'uptime_monitoring_service_configured' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Uptime monitoring is not configured. Set up external monitoring (UptimeRobot, New Relic, Datadog) to get immediate alerts when your site goes down.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/uptime-monitoring-not-configured',
			);
		}

		return null;
	}
}
