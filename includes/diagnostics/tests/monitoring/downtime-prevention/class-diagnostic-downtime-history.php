<?php
/**
 * Downtime History Tracking Diagnostic
 *
 * Analyzes past outages and downtime patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Downtime History Tracking Diagnostic Class
 *
 * Checks past outage patterns to prevent future downtime.
 * Like reviewing security incident logs to improve protection.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Downtime_History extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'downtime-history';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Downtime History Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes past outages and downtime patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the downtime history diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if concerning downtime detected, null otherwise.
	 */
	public static function check() {
		// Get downtime events from the last 30 days.
		$downtime_events = get_option( 'wpshadow_downtime_history', array() );

		// Filter to last 30 days.
		$thirty_days_ago = time() - ( 30 * DAY_IN_SECONDS );
		$recent_events = array_filter(
			$downtime_events,
			function( $event ) use ( $thirty_days_ago ) {
				return isset( $event['timestamp'] ) && $event['timestamp'] >= $thirty_days_ago;
			}
		);

		$event_count = count( $recent_events );

		// No recent downtime (great!).
		if ( 0 === $event_count ) {
			return null;
		}

		// Calculate total downtime duration.
		$total_downtime_seconds = 0;
		foreach ( $recent_events as $event ) {
			$duration = $event['duration'] ?? 0;
			$total_downtime_seconds += $duration;
		}

		$total_downtime_hours = $total_downtime_seconds / 3600;

		// Critical: Frequent or long downtime.
		if ( $event_count >= 10 || $total_downtime_hours > 24 ) {
			$issues = array();

			// Analyze causes if available.
			$causes = array();
			foreach ( $recent_events as $event ) {
				$cause = $event['cause'] ?? 'unknown';
				if ( ! isset( $causes[ $cause ] ) ) {
					$causes[ $cause ] = 0;
				}
				$causes[ $cause ]++;
			}
			arsort( $causes );
			$primary_cause = array_key_first( $causes );

			return array(
				'id'           => self::$slug . '-frequent',
				'title'        => __( 'Frequent Downtime Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: number of outages, 2: total downtime */
					__( 'Your site had %1$d outages in the last 30 days, totaling %2$s of downtime (like a store that keeps closing unexpectedly). This hurts visitor trust, search rankings, and revenue. Common causes: server issues, plugin conflicts, resource exhaustion, or attacks. Review your downtime logs to identify patterns and address the root cause. Consider upgrading hosting if server-related.', 'wpshadow' ),
					$event_count,
					human_time_diff( 0, $total_downtime_seconds )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/reduce-downtime?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'event_count'        => $event_count,
					'total_downtime'     => $total_downtime_seconds,
					'downtime_hours'     => $total_downtime_hours,
					'primary_cause'      => $primary_cause,
					'cause_breakdown'    => $causes,
				),
			);
		}

		// High: Some downtime but not critical.
		if ( $event_count >= 3 || $total_downtime_hours > 4 ) {
			return array(
				'id'           => self::$slug . '-concerning',
				'title'        => __( 'Recurring Downtime Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: number of outages, 2: total downtime */
					__( 'Your site had %1$d outages in the last 30 days (%2$s total downtime). While not critical yet, patterns like this tend to get worse over time (like small cracks in a building). Review your downtime logs to identify causes—common culprits: plugin conflicts, memory limits, slow database queries, or hosting issues. Addressing these now prevents bigger problems later.', 'wpshadow' ),
					$event_count,
					human_time_diff( 0, $total_downtime_seconds )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/reduce-downtime?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'event_count'    => $event_count,
					'total_downtime' => $total_downtime_seconds,
					'downtime_hours' => $total_downtime_hours,
				),
			);
		}

		// Low: Minimal downtime (acceptable).
		return array(
			'id'           => self::$slug . '-minor',
			'title'        => __( 'Minor Downtime Recorded', 'wpshadow' ),
			'description'  => sprintf(
				/* translators: 1: number of outages, 2: total downtime */
				__( 'Your site had %1$d brief outage(s) in the last 30 days (%2$s total). This is generally acceptable—occasional downtime happens (like occasional traffic jams). However, keep an eye on patterns. If downtime increases, investigate causes proactively. Most uptime monitoring services provide detailed incident reports.', 'wpshadow' ),
				$event_count,
				human_time_diff( 0, $total_downtime_seconds )
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/uptime-best-practices?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'event_count'    => $event_count,
				'total_downtime' => $total_downtime_seconds,
			),
		);
	}
}
