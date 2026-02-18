<?php
/**
 * KPI Logging Diagnostic
 *
 * Checks whether activity logging is capturing KPI events.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Monitoring
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KPI Logging Diagnostic Class
 *
 * Verifies that activity logging contains data.
 *
 * @since 1.6035.1400
 */
class Diagnostic_KPI_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'kpi-logging';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Activity Not Logged for KPI Tracking';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if activity logging contains KPI events';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		if ( ! class_exists( Activity_Logger::class ) ) {
			$issues[] = __( 'Activity logger not available', 'wpshadow' );
		}

		$activities = Activity_Logger::get_activities();
		$stats['activity_count'] = isset( $activities['total'] ) ? (int) $activities['total'] : 0;

		if ( $stats['activity_count'] < 1 ) {
			$issues[] = __( 'No activity logs found for KPI tracking', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Tracking activity makes it easy to show the value of features over time. Logging key events helps you measure what is working.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/kpi-logging',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
