<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Treatments\Treatment_Registry;
use WPShadow\Core\KPI_Tracker;

/**
 * WPShadow Guardian Auto-Fix Executor
 *
 * Safely executes approved treatments with full safeguards.
 * Manages backup creation, execution, and rollback.
 *
 * Safety Features:
 * - Backup before every fix
 * - Anomaly detection
 * - Rate limiting
 * - Execution logging
 * - Rollback capability
 *
 * Philosophy: Safety first. Never break the site.
 */
class Auto_Fix_Executor {

	/**
	 * Execute scheduled auto-fixes
	 *
	 * Main entry point for cron job execution.
	 * Runs all approved, applicable treatments.
	 *
	 * @return array Execution summary
	 */
	public static function execute_scheduled_fixes(): array {
		// Check if auto-fix enabled
		if ( ! get_option( 'wpshadow_guardian_auto_fix_enabled' ) ) {
			return array(
				'executed' => false,
				'reason'   => 'Auto-fix disabled by user',
			);
		}

		// Detect anomalies first
		$anomalies = Anomaly_Detector::get_summary();
		if ( ! $anomalies['can_proceed'] ) {
			Guardian_Activity_Logger::log_auto_fix_paused( $anomalies );
			return array(
				'executed'  => false,
				'reason'    => 'Anomalies detected - execution paused',
				'anomalies' => $anomalies,
			);
		}

		// Get approved treatments
		$safe_fixes = Auto_Fix_Policy_Manager::get_safe_fixes();
		if ( empty( $safe_fixes ) ) {
			return array(
				'executed' => true,
				'fixed'    => 0,
				'message'  => 'No treatments approved for auto-fix',
			);
		}

		$results = array(
			'executed'       => true,
			'total_approved' => count( $safe_fixes ),
			'fixed'          => 0,
			'failed'         => 0,
			'skipped'        => 0,
			'treatments'     => array(),
		);

		$max_per_run       = Auto_Fix_Policy_Manager::get_max_treatments_per_run();
		$continue_on_error = Auto_Fix_Policy_Manager::should_continue_on_error();

		// Execute each approved treatment
		$executed_count = 0;
		foreach ( $safe_fixes as $treatment_id ) {
			// Respect max per run
			if ( $executed_count >= $max_per_run ) {
				++$results['skipped'];
				continue;
			}

			// Execute treatment
			$result = self::execute_treatment( $treatment_id );
			++$executed_count;

			if ( $result['success'] ) {
				++$results['fixed'];
			} else {
				++$results['failed'];

				if ( ! $continue_on_error ) {
					break; // Stop on first error
				}
			}

			$results['treatments'][] = $result;
		}

		// Log summary
		Guardian_Activity_Logger::log_auto_fix_execution( $results );

		// Clear anomaly baselines for next check
		Anomaly_Detector::clear_baselines();

		return $results;
	}

	/**
	 * Execute single treatment with full safeguards
	 *
	 * @param string $treatment_id Treatment to apply
	 *
	 * @return array Execution result
	 */
	private static function execute_treatment( string $treatment_id ): array {
		$treatment_id = sanitize_key( $treatment_id );

		$result = array(
			'treatment_id' => $treatment_id,
			'success'      => false,
			'backup_id'    => '',
			'message'      => '',
			'timestamp'    => current_time( 'mysql' ),
		);

		try {
			// Get treatment class
			$treatment = Treatment_Registry::get( $treatment_id );
			if ( ! $treatment ) {
				$result['message'] = 'Treatment not found';
				return $result;
			}

			// Check if applicable
			if ( ! method_exists( $treatment, 'has_issues' ) || ! $treatment::has_issues() ) {
				$result['message'] = 'No issues detected for this treatment';
				$result['skipped'] = true;
				return $result;
			}

			// Check if can apply
			if ( ! method_exists( $treatment, 'can_apply' ) || ! $treatment::can_apply() ) {
				$result['message'] = 'Treatment cannot be applied in current state';
				return $result;
			}

			// Create backup before fix
			$backup_id           = Guardian_Backup_Manager::create_backup(
				'auto_fix_' . $treatment_id,
				$treatment::get_name()
			);
			$result['backup_id'] = $backup_id;

			// Apply treatment
			$fix_result = $treatment::apply();

			if ( ! $fix_result ) {
				$result['message'] = 'Treatment application failed';

				// Attempt rollback
				if ( ! Guardian_Backup_Manager::restore_backup( $backup_id ) ) {
					$result['message'] .= ' - ROLLBACK FAILED';
				} else {
					$result['message'] .= ' - Rolled back to backup';
				}

				return $result;
			}

			// Success!
			$result['success'] = true;
			$result['message'] = 'Treatment applied successfully';

			// Track KPI
			KPI_Tracker::record_treatment_applied( $treatment_id, 0 );

			// Log to activity
			Guardian_Activity_Logger::log_auto_fix( $treatment_id, true, $backup_id );

		} catch ( \Exception $e ) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			error_log( 'WPShadow Auto-Fix error: ' . $e->getMessage() );
		}

		return $result;
	}

	/**
	 * Execute treatment immediately (manual override)
	 *
	 * @param string $treatment_id Treatment to apply now
	 *
	 * @return array Result
	 */
	public static function execute_now( string $treatment_id ): array {
		return self::execute_treatment( $treatment_id );
	}

	/**
	 * Dry-run: predict what auto-fixes would do
	 *
	 * Doesn't actually apply fixes. Shows what would happen.
	 * Useful for showing users what Guardian would do.
	 *
	 * @return array Predicted execution
	 */
	public static function preview_auto_fixes(): array {
		$safe_fixes = Auto_Fix_Policy_Manager::get_safe_fixes();

		$preview = array(
			'timestamp'  => current_time( 'mysql' ),
			'total'      => count( $safe_fixes ),
			'applicable' => 0,
			'treatments' => array(),
		);

		foreach ( $safe_fixes as $treatment_id ) {
			try {
				$treatment = Treatment_Registry::get( $treatment_id );

				if ( ! $treatment || ! method_exists( $treatment, 'has_issues' ) ) {
					continue;
				}

				if ( ! $treatment::has_issues() ) {
					continue;
				}

				++$preview['applicable'];
				$preview['treatments'][] = array(
					'id'          => $treatment_id,
					'name'        => $treatment::get_name(),
					'would_apply' => true,
				);
			} catch ( \Exception $e ) {
				// Skip on error
			}
		}

		return $preview;
	}

	/**
	 * Get execution history
	 *
	 * @param int $limit Number of recent executions
	 *
	 * @return array Execution history
	 */
	public static function get_execution_history( int $limit = 20 ): array {
		$history = get_option( 'wpshadow_auto_fix_history', array() );
		return array_slice( array_reverse( $history ), 0, $limit );
	}

	/**
	 * Check if execution is in progress
	 *
	 * Prevents concurrent executions.
	 *
	 * @return bool Execution in progress
	 */
	public static function is_executing(): bool {
		$lock = get_transient( 'wpshadow_auto_fix_lock' );
		return ! empty( $lock );
	}

	/**
	 * Get last execution time
	 *
	 * @return string|null Last execution timestamp or null
	 */
	public static function get_last_execution_time(): ?string {
		$history = get_option( 'wpshadow_auto_fix_history', array() );
		if ( empty( $history ) ) {
			return null;
		}

		$last = end( $history );
		return $last['timestamp'] ?? null;
	}

	/**
	 * Get execution statistics
	 *
	 * @return array Stats
	 */
	public static function get_statistics(): array {
		$history = get_option( 'wpshadow_auto_fix_history', array() );

		$stats = array(
			'total_runs'   => count( $history ),
			'total_fixed'  => 0,
			'total_failed' => 0,
			'last_run'     => self::get_last_execution_time(),
		);

		foreach ( $history as $execution ) {
			$stats['total_fixed']  += (int) ( $execution['fixed'] ?? 0 );
			$stats['total_failed'] += (int) ( $execution['failed'] ?? 0 );
		}

		if ( $stats['total_runs'] > 0 ) {
			$stats['success_rate'] = round(
				( $stats['total_fixed'] / ( $stats['total_fixed'] + $stats['total_failed'] ) ) * 100,
				2
			);
		}

		return $stats;
	}
}
