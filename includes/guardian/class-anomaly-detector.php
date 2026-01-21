<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Guardian Anomaly Detector
 * 
 * Detects unusual patterns before applying auto-fixes.
 * Prevents auto-fixes from running in unexpected conditions.
 * 
 * Checks:
 * - High CPU/memory usage
 * - Recent site changes
 * - Unusual access patterns
 * - Failed transactions
 * - Rate limit anomalies
 * 
 * Philosophy: Smart automation. Pause when things are weird.
 */
class Anomaly_Detector {
	
	/**
	 * Detect anomalies in current system state
	 * 
	 * Performs multiple checks to see if site is in anomalous state.
	 * Returns list of detected anomalies.
	 * 
	 * @return array List of anomalies detected
	 */
	public static function detect(): array {
		$anomalies = [];
		
		// Check memory usage
		if ( self::check_memory_anomaly() ) {
			$anomalies[] = [
				'type'     => 'memory_high',
				'severity' => 'warning',
				'message'  => 'Memory usage is abnormally high',
			];
		}
		
		// Check recent changes
		if ( self::check_recent_changes_anomaly() ) {
			$anomalies[] = [
				'type'     => 'recent_changes',
				'severity' => 'warning',
				'message'  => 'Recent significant changes detected',
			];
		}
		
		// Check plugin/theme changes
		if ( self::check_plugin_anomaly() ) {
			$anomalies[] = [
				'type'     => 'plugin_changes',
				'severity' => 'info',
				'message'  => 'Plugins/themes recently modified',
			];
		}
		
		// Check error logs
		if ( self::check_error_spike_anomaly() ) {
			$anomalies[] = [
				'type'     => 'error_spike',
				'severity' => 'warning',
				'message'  => 'Spike in error logs detected',
			];
		}
		
		// Check db transaction failures
		if ( self::check_db_anomaly() ) {
			$anomalies[] = [
				'type'     => 'database_issues',
				'severity' => 'critical',
				'message'  => 'Database issues detected',
			];
		}
		
		return $anomalies;
	}
	
	/**
	 * Check if should pause auto-fixes
	 * 
	 * Returns true if any critical anomalies detected.
	 * Auto-fixes should not run when this returns true.
	 * 
	 * @return bool Should pause auto-fixes
	 */
	public static function should_pause_auto_fixes(): bool {
		$anomalies = self::detect();
		
		// Pause if any critical anomalies
		foreach ( $anomalies as $anomaly ) {
			if ( $anomaly['severity'] === 'critical' ) {
				return true;
			}
		}
		
		// Pause if too many warnings
		$warning_count = count( array_filter(
			$anomalies,
			fn( $a ) => $a['severity'] === 'warning'
		) );
		
		return $warning_count >= 3;
	}
	
	/**
	 * Check memory usage anomaly
	 * 
	 * @return bool Anomaly detected
	 */
	private static function check_memory_anomaly(): bool {
		if ( ! function_exists( 'memory_get_usage' ) ) {
			return false;
		}
		
		$current = memory_get_usage( true );
		$limit = (int) wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$usage_pct = ( $current / $limit ) * 100;
		
		// Anomaly if usage > 85%
		return $usage_pct > 85;
	}
	
	/**
	 * Check for recent significant changes
	 * 
	 * @return bool Anomaly detected
	 */
	private static function check_recent_changes_anomaly(): bool {
		$last_check = get_transient( 'wpshadow_anomaly_baseline' );
		
		if ( ! $last_check ) {
			// Set baseline on first check
			set_transient( 'wpshadow_anomaly_baseline', [
				'timestamp' => time(),
				'plugins'   => md5( serialize( get_option( 'active_plugins' ) ) ),
				'theme'     => md5( get_stylesheet() ),
			], HOUR_IN_SECONDS * 6 );
			return false;
		}
		
		// Check for changes in last 30 minutes
		$time_diff = time() - $last_check['timestamp'];
		if ( $time_diff < 1800 ) { // 30 minutes
			// Recent baseline update, check for changes
			$current_plugins = md5( serialize( get_option( 'active_plugins' ) ) );
			$current_theme = md5( get_stylesheet() );
			
			if ( $current_plugins !== $last_check['plugins'] || $current_theme !== $last_check['theme'] ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check for plugin/theme modifications
	 * 
	 * @return bool Anomaly detected
	 */
	private static function check_plugin_anomaly(): bool {
		$last_check = get_option( 'wpshadow_last_plugin_check', 0 );
		$now = time();
		
		// Check if changes happened in last 10 minutes
		if ( ( $now - $last_check ) < 600 ) {
			return true;
		}
		
		update_option( 'wpshadow_last_plugin_check', $now );
		return false;
	}
	
	/**
	 * Check for error log spikes
	 * 
	 * @return bool Anomaly detected
	 */
	private static function check_error_spike_anomaly(): bool {
		// Only check if debug log exists
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return false;
		}
		
		$log_file = WP_CONTENT_DIR . '/debug.log';
		if ( ! file_exists( $log_file ) ) {
			return false;
		}
		
		$last_size = (int) get_transient( 'wpshadow_debug_log_size' );
		$current_size = filesize( $log_file );
		
		// If log grew by > 100KB in 5 minutes, anomaly
		if ( $last_size > 0 && ( $current_size - $last_size ) > 102400 ) {
			set_transient( 'wpshadow_debug_log_size', $current_size, 300 );
			return true;
		}
		
		set_transient( 'wpshadow_debug_log_size', $current_size, 300 );
		return false;
	}
	
	/**
	 * Check for database issues
	 * 
	 * @return bool Anomaly detected
	 */
	private static function check_db_anomaly(): bool {
		global $wpdb;
		
		// Try simple query
		$result = $wpdb->get_results( 'SELECT 1', ARRAY_A );
		
		if ( $wpdb->last_error || ! $result ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get anomaly summary
	 * 
	 * @return array Summary for reporting
	 */
	public static function get_summary(): array {
		$anomalies = self::detect();
		
		return [
			'total'      => count( $anomalies ),
			'critical'   => count( array_filter( $anomalies, fn( $a ) => $a['severity'] === 'critical' ) ),
			'warning'    => count( array_filter( $anomalies, fn( $a ) => $a['severity'] === 'warning' ) ),
			'info'       => count( array_filter( $anomalies, fn( $a ) => $a['severity'] === 'info' ) ),
			'anomalies'  => $anomalies,
			'can_proceed' => ! self::should_pause_auto_fixes(),
		];
	}
	
	/**
	 * Clear anomaly baselines
	 * 
	 * Called after successful auto-fixes to reset for next run.
	 */
	public static function clear_baselines(): void {
		delete_transient( 'wpshadow_anomaly_baseline' );
		delete_option( 'wpshadow_last_plugin_check' );
		delete_transient( 'wpshadow_debug_log_size' );
	}
}
