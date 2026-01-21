<?php
/**
 * KPI Tracker for WPShadow
 *
 * Tracks key performance indicators to prove value delivered
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * KPI tracking system for monitoring fixes and improvements
 */
class KPI_Tracker {
	/**
	 * Log a finding detection
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $severity   Severity level (critical, high, medium, low).
	 * @return void
	 */
	public static function log_finding_detected( $finding_id, $severity = 'medium' ) {
		$tracking = self::get_tracking_data();
		
		if ( ! isset( $tracking['findings_detected'] ) ) {
			$tracking['findings_detected'] = array();
		}
		
		$key = $finding_id . '_' . gmdate( 'Y-m-d' );
		if ( ! isset( $tracking['findings_detected'][ $key ] ) ) {
			$tracking['findings_detected'][ $key ] = array(
				'finding_id' => $finding_id,
				'severity'   => $severity,
				'date'       => gmdate( 'Y-m-d H:i:s' ),
				'count'      => 0,
			);
		}
		
		$tracking['findings_detected'][ $key ]['count']++;

		/**
		 * Fires when a finding is detected.
		 *
		 * @param string $finding_id Finding identifier.
		 * @param string $severity   Severity level.
		 */
		do_action( 'wpshadow_finding_detected', $finding_id, $severity );

		self::save_tracking_data( $tracking );
	}
	
	/**
	 * Log a fix application
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $method     How it was fixed (auto, manual, user).
	 * @return void
	 */
	public static function log_fix_applied( $finding_id, $method = 'auto' ) {
		$tracking = self::get_tracking_data();
		
		if ( ! isset( $tracking['fixes_applied'] ) ) {
			$tracking['fixes_applied'] = array();
		}
		
		$tracking['fixes_applied'][] = array(
			'finding_id' => $finding_id,
			'method'     => $method,
			'date'       => gmdate( 'Y-m-d H:i:s' ),
		);
		
		self::save_tracking_data( $tracking );
	}
	
	/**
	 * Log a finding dismissal
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $reason     Optional reason for dismissal.
	 * @return void
	 */
	public static function log_finding_dismissed( $finding_id, $reason = 'user-choice' ) {
		$tracking = self::get_tracking_data();
		
		if ( ! isset( $tracking['findings_dismissed'] ) ) {
			$tracking['findings_dismissed'] = array();
		}
		
		$tracking['findings_dismissed'][] = array(
			'finding_id' => $finding_id,
			'reason'     => $reason,
			'date'       => gmdate( 'Y-m-d H:i:s' ),
		);
		
		self::save_tracking_data( $tracking );
	}
	
	/**
	 * Get KPI summary
	 *
	 * @return array KPI data.
	 */
	public static function get_kpi_summary() {
		$tracking = self::get_tracking_data();
		
		$findings_detected  = ! empty( $tracking['findings_detected'] ) ? count( $tracking['findings_detected'] ) : 0;
		$fixes_applied      = ! empty( $tracking['fixes_applied'] ) ? count( $tracking['fixes_applied'] ) : 0;
		$findings_dismissed = ! empty( $tracking['findings_dismissed'] ) ? count( $tracking['findings_dismissed'] ) : 0;
		
		return array(
			'findings_detected'  => $findings_detected,
			'fixes_applied'      => $fixes_applied,
			'fixes_percentage'   => $findings_detected > 0 ? round( ( $fixes_applied / $findings_detected ) * 100, 1 ) : 0,
			'findings_dismissed' => $findings_dismissed,
			'time_saved'         => self::calculate_time_saved( $fixes_applied ),
		);
	}
	
	/**
	 * Calculate estimated time saved (rough estimate: 15 min per fix)
	 *
	 * @param int $fixes_count Number of fixes applied.
	 * @return string Formatted time saved.
	 */
	private static function calculate_time_saved( $fixes_count ) {
		$minutes = $fixes_count * 15; // Estimate 15 min per fix
		$hours   = intval( $minutes / 60 );
		$mins    = $minutes % 60;
		
		if ( $hours > 0 ) {
			return sprintf( '%dh %dm', $hours, $mins );
		}
		return sprintf( '%dm', $mins );
	}
	
	/**
	 * Get all tracking data
	 *
	 * @return array Tracking data.
	 */
	private static function get_tracking_data() {
		$data = get_option( 'wpshadow_kpi_tracking', array() );
		return is_array( $data ) ? $data : array();
	}
	
	/**
	 * Save tracking data
	 *
	 * @param array $data Data to save.
	 * @return void
	 */
	private static function save_tracking_data( $data ) {
		// Keep only last 90 days of data
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
		
		if ( ! empty( $data['findings_detected'] ) ) {
			$data['findings_detected'] = array_filter(
				$data['findings_detected'],
				function( $item ) use ( $cutoff_date ) {
					return $item['date'] >= $cutoff_date;
				}
			);
		}
		
		if ( ! empty( $data['fixes_applied'] ) ) {
			$data['fixes_applied'] = array_filter(
				$data['fixes_applied'],
				function( $item ) use ( $cutoff_date ) {
					return $item['date'] >= $cutoff_date;
				}
			);
		}
		
		if ( ! empty( $data['findings_dismissed'] ) ) {
			$data['findings_dismissed'] = array_filter(
				$data['findings_dismissed'],
				function( $item ) use ( $cutoff_date ) {
					return $item['date'] >= $cutoff_date;
				}
			);
		}
		
		update_option( 'wpshadow_kpi_tracking', $data );
	}
	
	/**
	 * Reset all KPI data (useful for testing)
	 *
	 * @return void
	 */
	public static function reset() {
		delete_option( 'wpshadow_kpi_tracking' );
	}

	/**
	 * Get dark mode adoption metrics
	 *
	 * @return array Dark mode adoption data.
	 */
	public static function get_dark_mode_adoption() {
		$adoption_data = get_option( 'wpshadow_dark_mode_adoption', array() );
		
		// Return with defaults if empty
		return wp_parse_args( $adoption_data, array(
			'total_users'       => 0,
			'dark_mode_users'   => 0,
			'auto_mode_users'   => 0,
			'light_mode_users'  => 0,
			'last_updated'      => 'Never',
			'adoption_rate'     => 0,
		) );
	}
}
