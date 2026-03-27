<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Guardian\Auto_Fix_Executor;
use WPShadow\Guardian\Compliance_Checker;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Execute Auto-Fix
 *
 * Manual trigger for auto-fix execution.
 * User can run auto-fixes immediately instead of waiting for schedule.
 *
 * POST Parameters:
 * - treatment (required): Treatment class to execute
 * - force (optional): Skip anomaly checks
 */
class Execute_Auto_Fix_Command extends Command_Base {

	/**
	 * Execute the command
	 *
	 * @return array Result
	 */
	protected function execute(): array {
		// Get treatment to execute
		$treatment = sanitize_text_field( $this->get_param( 'treatment' ) );
		if ( empty( $treatment ) ) {
			return $this->error( 'Treatment class required' );
		}

		// Get force flag
		$force = filter_var( $this->get_param( 'force' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false;

		// Validate treatment
		$validation = Compliance_Checker::validate_treatment( $treatment );
		if ( ! $validation['compliant'] ) {
			return $this->error(
				'Treatment not compliant for auto-fix: ' . implode( ', ', $validation['issues'] )
			);
		}

		try {
			// Execute the treatment
			$result = Auto_Fix_Executor::execute_treatment(
				$treatment,
				array(
					'force'  => $force,
					'reason' => 'Manual execution',
				)
			);

			if ( $result['success'] ) {
				KPI_Tracker::record_action( 'auto_fix_executed_manual', 1 );

				return $this->success(
					array(
						'message'  => 'Auto-fix executed successfully',
						'backup'   => $result['backup_id'] ?? null,
						'duration' => $result['duration'] ?? 0,
					)
				);
			} else {
				return $this->error( $result['error'] ?? 'Execution failed' );
			}
		} catch ( \Exception $e ) {
			return $this->error( 'Execution error: ' . $e->getMessage() );
		}
	}

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'execute_auto_fix';
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return 'Manually execute an auto-fix treatment';
	}
}
