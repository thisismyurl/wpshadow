<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * WPShadow Guardian Activity Logger
 *
 * Logs all WPShadow Guardian actions for transparency and auditing.
 * Maintains activity history for dashboard display.
 * Keeps last 500 entries (memory-efficient).
 *
 * Data Storage:
 * - wpshadow_guardian_activity_log: Array of activity entries (last 500)
 */
class Guardian_Activity_Logger {

	/**
	 * Log health check execution
	 *
	 * @param array $findings Findings detected in health check
	 */
	public static function log_health_check( array $findings ): void {
		$critical_count = count(
			array_filter(
				$findings,
				fn( $f ) => $f['severity'] === 'critical'
			)
		);

		$log = array(
			'timestamp'      => current_time( 'mysql' ),
			'type'           => 'health_check',
			'findings_total' => count( $findings ),
			'critical_count' => $critical_count,
			'status'         => $critical_count > 0 ? 'warning' : 'ok',
		);

		self::append_to_log( $log );
	}

	/**
	 * Log auto-fix execution
	 *
	 * @param string $treatment_id Treatment that was applied
	 * @param bool   $success Whether fix was successful
	 * @param string $backup_id Backup ID if backup created
	 */
	public static function log_auto_fix(
		string $treatment_id,
		bool $success,
		string $backup_id = ''
	): void {
		$log = array(
			'timestamp' => current_time( 'mysql' ),
			'type'      => 'auto_fix',
			'treatment' => sanitize_text_field( $treatment_id ),
			'success'   => $success,
			'backup_id' => $backup_id,
		);

		self::append_to_log( $log );
	}

	/**
	 * Log anomaly detection
	 *
	 * @param array $anomalies Anomalies detected
	 */
	public static function log_anomalies( array $anomalies ): void {
		if ( empty( $anomalies ) ) {
			return; // No anomalies to log
		}

		$log = array(
			'timestamp'       => current_time( 'mysql' ),
			'type'            => 'anomaly_detected',
			'anomalies_count' => count( $anomalies ),
			'anomalies'       => array_map(
				function ( $a ) {
					return array(
						'type'     => $a['type'],
						'severity' => $a['severity'],
					);
				},
				$anomalies
			),
		);

		self::append_to_log( $log );
	}

	/**
	 * Log WPShadow Guardian settings change
	 *
	 * @param array $settings New settings
	 */
	public static function log_settings_change( array $settings ): void {
		$log = array(
			'timestamp'        => current_time( 'mysql' ),
			'type'             => 'settings_changed',
			'enabled'          => $settings['enabled'] ?? false,
			'auto_fix_enabled' => $settings['auto_fix_enabled'] ?? false,
		);

		self::append_to_log( $log );
	}

	/**
	 * Get activity log for display
	 *
	 * @param int $limit Number of entries to return
	 * @param string $type Optional: filter by entry type
	 *
	 * @return array Activity log entries
	 */
	public static function get_activity_log( int $limit = 50, string $type = '' ): array {
		$logs = get_option( 'wpshadow_guardian_activity_log', array() );

		// Filter by type if specified
		if ( ! empty( $type ) ) {
			$logs = array_filter(
				$logs,
				fn( $l ) => $l['type'] === $type
			);
		}

		// Return most recent
		return array_slice( $logs, -$limit );
	}

	/**
	 * Get activity statistics
	 *
	 * @return array Activity stats
	 */
	public static function get_statistics(): array {
		$logs = get_option( 'wpshadow_guardian_activity_log', array() );

		$stats = array(
			'total_entries'      => count( $logs ),
			'health_checks'      => 0,
			'auto_fixes_total'   => 0,
			'auto_fixes_success' => 0,
			'anomalies_detected' => 0,
		);

		foreach ( $logs as $entry ) {
			switch ( $entry['type'] ) {
				case 'health_check':
					++$stats['health_checks'];
					break;
				case 'auto_fix':
					++$stats['auto_fixes_total'];
					if ( $entry['success'] ) {
						++$stats['auto_fixes_success'];
					}
					break;
				case 'anomaly_detected':
					++$stats['anomalies_detected'];
					break;
			}
		}

		return $stats;
	}

	/**
	 * Clear activity log
	 *
	 * Called manually or when uninstalling WPShadow Guardian.
	 */
	public static function clear_log(): void {
		delete_option( 'wpshadow_guardian_activity_log' );
	}

	/**
	 * Append entry to activity log
	 *
	 * Keeps only last 500 entries (memory-efficient).
	 *
	 * @param array $entry Log entry
	 */
	private static function append_to_log( array $entry ): void {
		$logs = get_option( 'wpshadow_guardian_activity_log', array() );

		$logs[] = $entry;

		// Keep only last 500 entries
		$logs = array_slice( $logs, -500 );

		update_option( 'wpshadow_guardian_activity_log', $logs );
	}
}
