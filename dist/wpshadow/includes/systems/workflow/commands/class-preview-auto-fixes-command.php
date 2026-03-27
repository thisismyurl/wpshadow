<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Guardian\Auto_Fix_Executor;
use WPShadow\Guardian\Anomaly_Detector;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Preview Auto-Fixes
 *
 * Show what auto-fixes would be applied without executing them.
 * Dry-run preview with anomaly detection.
 *
 * POST Parameters:
 * - include_warnings (optional): Include anomaly warnings
 */
class Preview_Auto_Fixes_Command extends Command_Base {
	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'preview_auto_fixes';
	}

	/**
	 * Execute the command
	 *
	 * @return array Result
	 */
	protected function execute(): array {
		$include_warnings = filter_var(
			$this->get_param( 'include_warnings' ),
			FILTER_VALIDATE_BOOLEAN,
			FILTER_NULL_ON_FAILURE
		) ?? true;

		try {
			// Get preview of what would be fixed
			$preview = Auto_Fix_Executor::preview_auto_fixes();

			$result = array(
				'treatments' => $preview['treatments'] ?? array(),
				'duration'   => $preview['estimated_duration'] ?? 0,
				'count'      => count( $preview['treatments'] ?? array() ),
			);

			// Add anomaly detection if requested
			if ( $include_warnings ) {
				$anomalies = Anomaly_Detector::detect();

				$result['anomalies'] = array(
					'detected'     => ! empty( $anomalies ),
					'should_pause' => Anomaly_Detector::should_pause_auto_fixes(),
					'warnings'     => $anomalies,
				);
			}

			KPI_Tracker::record_action( 'auto_fix_previewed', 1 );

			return $this->success( $result );
		} catch ( \Exception $e ) {
			return $this->error( 'Preview error: ' . $e->getMessage() );
		}
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return 'Preview auto-fixes without executing them';
	}
}
