<?php
/**
 * Health History Analytics
 *
 * Records and manages site health snapshots over time for trend analysis.
 *
 * @package    WPShadow
 * @subpackage Analytics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health History Class
 *
 * Tracks site health metrics over time and provides data for visualization.
 *
 * @since 1.6093.1200
 */
class Health_History {

	/**
	 * Option name for storing health history data.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'wpshadow_health_history';

	/**
	 * Maximum number of days to retain history.
	 *
	 * @var int
	 */
	const MAX_HISTORY_DAYS = 90;

	/**
	 * Initialize health history tracking.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		// Record snapshot after diagnostics complete.
		add_action( 'wpshadow_diagnostics_scan_complete', array( __CLASS__, 'record_snapshot' ) );
		
		// Daily cleanup of old data.
		add_action( 'wpshadow_daily_cleanup', array( __CLASS__, 'trim_old_snapshots' ) );
		
		// Schedule daily snapshot if not already scheduled.
		if ( ! wp_next_scheduled( 'wpshadow_daily_health_snapshot' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_daily_health_snapshot' );
		}
		
		add_action( 'wpshadow_daily_health_snapshot', array( __CLASS__, 'record_snapshot' ) );
	}

	/**
	 * Record a health snapshot with current metrics.
	 *
	 * @since 1.6093.1200
	 * @param  array $findings Optional. Findings data if available.
	 * @return bool True on success, false on failure.
	 */
	public static function record_snapshot( $findings = array() ) {
		$history = self::get_history();
		$today = gmdate( 'Y-m-d' );

		// Calculate health scores.
		$snapshot = array(
			'date'            => $today,
			'timestamp'       => time(),
			'overall_health'  => self::calculate_overall_health( $findings ),
			'security'        => self::calculate_category_health( 'security', $findings ),
			'performance'     => self::calculate_category_health( 'performance', $findings ),
			'quality'         => self::calculate_category_health( 'quality', $findings ),
			'configuration'   => self::calculate_category_health( 'configuration', $findings ),
			'import_export'   => self::calculate_category_health( 'import-export', $findings ),
			'tools'           => self::calculate_category_health( 'tools', $findings ),
			'issues_count'    => count( $findings ),
			'critical_count'  => self::count_by_severity( 'critical', $findings ),
			'high_count'      => self::count_by_severity( 'high', $findings ),
			'medium_count'    => self::count_by_severity( 'medium', $findings ),
			'low_count'       => self::count_by_severity( 'low', $findings ),
		);

		// Replace today's snapshot if it exists.
		$history[ $today ] = $snapshot;

		// Trim to max days.
		$history = array_slice( $history, -self::MAX_HISTORY_DAYS, null, true );

		return update_option( self::OPTION_NAME, $history, false );
	}

	/**
	 * Get health history data.
	 *
	 * @since 1.6093.1200
	 * @param  int $days Optional. Number of days to retrieve. Default all.
	 * @return array Health history keyed by date.
	 */
	public static function get_history( $days = 0 ) {
		$history = get_option( self::OPTION_NAME, array() );

		if ( $days > 0 ) {
			$cutoff_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
			$history = array_filter(
				$history,
				function( $date ) use ( $cutoff_date ) {
					return $date >= $cutoff_date;
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		return $history;
	}

	/**
	 * Calculate overall health score.
	 *
	 * @since 1.6093.1200
	 * @param  array $findings Diagnostic findings.
	 * @return int Health score 0-100.
	 */
	private static function calculate_overall_health( $findings ) {
		if ( empty( $findings ) ) {
			return 100;
		}

		// Weighted scoring based on severity.
		$total_weight = 0;
		$severity_weights = array(
			'critical' => 10,
			'high'     => 5,
			'medium'   => 2,
			'low'      => 1,
		);

		foreach ( $findings as $finding ) {
			$severity = $finding['severity'] ?? 'low';
			$total_weight += $severity_weights[ $severity ] ?? 1;
		}

		// Convert to 0-100 scale (assuming 100 weight = 0% health).
		$health = max( 0, 100 - $total_weight );

		return (int) $health;
	}

	/**
	 * Calculate category-specific health score.
	 *
	 * @since 1.6093.1200
	 * @param  string $category Category name.
	 * @param  array  $findings Diagnostic findings.
	 * @return int Health score 0-100.
	 */
	private static function calculate_category_health( $category, $findings ) {
		$category_findings = array_filter(
			$findings,
			function( $finding ) use ( $category ) {
				return isset( $finding['family'] ) && $finding['family'] === $category;
			}
		);

		return self::calculate_overall_health( $category_findings );
	}

	/**
	 * Count findings by severity level.
	 *
	 * @since 1.6093.1200
	 * @param  string $severity Severity level.
	 * @param  array  $findings Diagnostic findings.
	 * @return int Count of findings.
	 */
	private static function count_by_severity( $severity, $findings ) {
		return count(
			array_filter(
				$findings,
				function( $finding ) use ( $severity ) {
					return isset( $finding['severity'] ) && $finding['severity'] === $severity;
				}
			)
		);
	}

	/**
	 * Trim old snapshots beyond retention period.
	 *
	 * @since 1.6093.1200
	 * @return bool True on success.
	 */
	public static function trim_old_snapshots() {
		$history = get_option( self::OPTION_NAME, array() );
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-' . self::MAX_HISTORY_DAYS . ' days' ) );

		$history = array_filter(
			$history,
			function( $date ) use ( $cutoff_date ) {
				return $date >= $cutoff_date;
			},
			ARRAY_FILTER_USE_KEY
		);

		return update_option( self::OPTION_NAME, $history, false );
	}

	/**
	 * Get summary statistics for a date range.
	 *
	 * @since 1.6093.1200
	 * @param  int $days Number of days to analyze.
	 * @return array {
	 *     Summary statistics.
	 *
	 *     @type int   $health_change    Change in health score.
	 *     @type int   $issues_fixed     Number of issues resolved.
	 *     @type float $avg_health       Average health over period.
	 *     @type int   $best_health      Best health score.
	 *     @type int   $worst_health     Worst health score.
	 * }
	 */
	public static function get_summary( $days = 30 ) {
		$history = self::get_history( $days );

		if ( empty( $history ) ) {
			return array(
				'health_change' => 0,
				'issues_fixed'  => 0,
				'avg_health'    => 0,
				'best_health'   => 0,
				'worst_health'  => 0,
			);
		}

		$health_scores = array_column( $history, 'overall_health' );
		$first_health = reset( $health_scores );
		$last_health = end( $health_scores );

		$first_issues = reset( $history )['issues_count'] ?? 0;
		$last_issues = end( $history )['issues_count'] ?? 0;

		return array(
			'health_change' => $last_health - $first_health,
			'issues_fixed'  => max( 0, $first_issues - $last_issues ),
			'avg_health'    => (int) array_sum( $health_scores ) / count( $health_scores ),
			'best_health'   => max( $health_scores ),
			'worst_health'  => min( $health_scores ),
		);
	}

	/**
	 * Backfill historical data from activity log.
	 *
	 * @since 1.6093.1200
	 * @return int Number of snapshots created.
	 */
	public static function backfill_from_activity_log() {
		// This would integrate with the Activity Logger to create historical snapshots.
		// For now, return 0 as this requires Activity Logger integration.
		return 0;
	}
}
