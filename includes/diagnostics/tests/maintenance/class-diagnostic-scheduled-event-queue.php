<?php
/**
 * Diagnostic: Scheduled Events Queue
 *
 * Checks if the WP-Cron queue is backed up with overdue events.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Maintenance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Scheduled_Event_Queue
 *
 * Tests WP-Cron queue for overdue events.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Scheduled_Event_Queue extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'scheduled-event-queue';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Scheduled Events Queue';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for overdue WP-Cron events';

	/**
	 * Check scheduled event queue.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$cron = _get_cron_array();

		if ( empty( $cron ) || ! is_array( $cron ) ) {
			return null;
		}

		$now        = time();
		$overdue    = 0;
		$oldest_lag = 0;

		foreach ( $cron as $timestamp => $hooks ) {
			if ( $timestamp < $now ) {
				$overdue++;
				$lag = $now - $timestamp;
				if ( $lag > $oldest_lag ) {
					$oldest_lag = $lag;
				}
			}
		}

		$threshold = 5; // allow small backlog.

		if ( $overdue > $threshold ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP-Cron queue has overdue events. Cron may be disabled or blocked; scheduled tasks could be failing.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/scheduled_event_queue',
				'meta'        => array(
					'overdue_count' => $overdue,
					'oldest_lag_s'  => $oldest_lag,
				),
			);
		}

		return null;
	}
}
