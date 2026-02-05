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
		$crons = _get_cron_array();
		
		if ( empty( $crons ) ) {
			return null;
		}
		
		$current_time = time();
		$total_events = 0;
		$missed_events = 0;
		$frequent_events = 0;
		$event_counts = array();
		
		// Analyze cron events
		foreach ( $crons as $timestamp => $cron_jobs ) {
			foreach ( $cron_jobs as $hook => $jobs ) {
				foreach ( $jobs as $job ) {
					$total_events++;
					
					// Count missed events (past due by >1 hour)
					if ( $timestamp < ( $current_time - 3600 ) ) {
						$missed_events++;
					}
					
					// Count event frequency
					if ( ! isset( $event_counts[ $hook ] ) ) {
						$event_counts[ $hook ] = 0;
					}
					$event_counts[ $hook ]++;
				}
			}
		}
		
		// Find events with excessive scheduling
		foreach ( $event_counts as $hook => $count ) {
			if ( $count > 10 ) {
				$frequent_events++;
			}
		}
		
		$issues = array();
		$score  = 0;
		
		// Check for excessive total events
		if ( $total_events > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of scheduled events */
				__( '%d scheduled events (should be <50)', 'wpshadow' ),
				$total_events
			);
			$score += 25;
		}
		
		// Check for missed events
		if ( $missed_events > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of missed events */
				__( '%d missed/overdue cron jobs', 'wpshadow' ),
				$missed_events
			);
			$score += 30;
		}
		
		// Check for frequent scheduling
		if ( $frequent_events > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of frequently scheduled hooks */
				__( '%d hooks scheduled excessively (>10 times each)', 'wpshadow' ),
				$frequent_events
			);
			$score += 20;
		}
		
		// Check if DISABLE_WP_CRON is set
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			// This is actually good - system cron is better
			return null;
		}
		
		// If no issues
		if ( empty( $issues ) ) {
			return null;
		}
		
		$severity = 'medium';
		if ( $score > 50 ) {
			$severity = 'high';
		}
		
		// Get top frequent events
		arsort( $event_counts );
		$top_events = array_slice( $event_counts, 0, 5, true );
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of cron issues */
				__( 'Cron job issues detected: %s. WP-Cron runs on page load, so excessive or stuck jobs can slow page requests. Consider using system cron instead.', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => min( 100, $score ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-wp-cron',
			'meta'         => array(
				'total_events'    => $total_events,
				'missed_events'   => $missed_events,
				'frequent_events' => $frequent_events,
				'top_hooks'       => $top_events,
				'wp_cron_disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
				'recommendation'  => 'Set up system cron and define DISABLE_WP_CRON',
			),
		);
	}
}
