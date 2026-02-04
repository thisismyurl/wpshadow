<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Guardian\Auto_Fix_Policy_Manager;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Update Auto-Fix Policy
 *
 * Manage which treatments are approved for auto-execution.
 * Users can add/remove treatments from the safe-fixes whitelist.
 *
 * POST Parameters:
 * - action (required): 'approve', 'revoke', or 'get_policies'
 * - treatment (optional): Treatment class to manage
 * - execution_time (optional): When to run (cron_hourly, cron_daily, manual)
 * - max_treatments (optional): Max treatments per run
 */
class Update_Auto_Fix_Policy_Command extends Command_Base {

	/**
	 * Execute the command
	 *
	 * @return array Result
	 */
	protected function execute(): array {
		$action = sanitize_key( $this->get_param( 'action' ) );

		switch ( $action ) {
			case 'approve':
				return $this->handle_approve();

			case 'revoke':
				return $this->handle_revoke();

			case 'get_policies':
				return $this->handle_get_policies();

			case 'set_execution_time':
				return $this->handle_set_execution_time();

			case 'set_max_treatments':
				return $this->handle_set_max_treatments();

			default:
				return $this->error( 'Unknown action: ' . $action );
		}
	}

	/**
	 * Approve treatment for auto-fix
	 *
	 * @return array Result
	 */
	private function handle_approve(): array {
		$treatment = sanitize_text_field( $this->get_param( 'treatment' ) );

		if ( empty( $treatment ) ) {
			return $this->error( 'Treatment class required' );
		}

		Auto_Fix_Policy_Manager::approve_for_auto_fix( $treatment );

		KPI_Tracker::record_action( 'auto_fix_treatment_approved', 1 );

		return $this->success(
			array(
				'message'   => 'Treatment approved for auto-fix',
				'treatment' => $treatment,
			)
		);
	}

	/**
	 * Revoke auto-fix approval
	 *
	 * @return array Result
	 */
	private function handle_revoke(): array {
		$treatment = sanitize_text_field( $this->get_param( 'treatment' ) );

		if ( empty( $treatment ) ) {
			return $this->error( 'Treatment class required' );
		}

		Auto_Fix_Policy_Manager::revoke_auto_fix( $treatment );

		KPI_Tracker::record_action( 'auto_fix_treatment_revoked', 1 );

		return $this->success(
			array(
				'message'   => 'Treatment revoked from auto-fix',
				'treatment' => $treatment,
			)
		);
	}

	/**
	 * Get current policies
	 *
	 * @return array Result
	 */
	private function handle_get_policies(): array {
		$policies = Auto_Fix_Policy_Manager::get_policy_summary();

		return $this->success( $policies );
	}

	/**
	 * Set execution time
	 *
	 * @return array Result
	 */
	private function handle_set_execution_time(): array {
		$time = sanitize_key( $this->get_param( 'execution_time' ) );

		if ( ! in_array( $time, array( 'cron_hourly', 'cron_daily', 'manual' ), true ) ) {
			return $this->error( 'Invalid execution time' );
		}

		Auto_Fix_Policy_Manager::set_execution_time( $time );

		KPI_Tracker::record_action( 'auto_fix_execution_time_updated', 1 );

		return $this->success(
			array(
				'message' => 'Execution time updated',
				'time'    => $time,
			)
		);
	}

	/**
	 * Set max treatments per run
	 *
	 * @return array Result
	 */
	private function handle_set_max_treatments(): array {
		$max = intval( $this->get_param( 'max_treatments' ) );

		if ( $max < 1 || $max > 20 ) {
			return $this->error( 'Max treatments must be between 1 and 20' );
		}

		Auto_Fix_Policy_Manager::set_max_treatments_per_run( $max );

		KPI_Tracker::record_action( 'auto_fix_max_treatments_updated', 1 );

		return $this->success(
			array(
				'message'        => 'Max treatments per run updated',
				'max_treatments' => $max,
			)
		);
	}

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'update_auto_fix_policy';
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return 'Manage auto-fix policy and settings';
	}
}
