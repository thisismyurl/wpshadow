<?php
/**
 * Kanban Workflow Helper - Creates workflows from Kanban actions
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to create workflows from Kanban board actions
 */
class Kanban_Workflow_Helper {

	/**
	 * Create a scheduled workflow for heavy diagnostics
	 *
	 * @param array $config Configuration options
	 * @return array Created workflow data
	 */
	public static function create_heavy_test_workflow( $config = array() ) {
		$defaults = array(
			'schedule_time' => '02:00',
			'days'          => array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ),
			'email'         => get_option( 'admin_email' ),
			'is_temporary'  => false,
		);

		$config = wp_parse_args( $config, $defaults );

		$blocks = array(
			array(
				'type'   => 'trigger',
				'id'     => 'time_trigger',
				'config' => array(
					'time' => $config['schedule_time'],
					'days' => $config['days'],
				),
			),
			array(
				'type'   => 'action',
				'id'     => 'run_diagnostic',
				'config' => array(
					'diagnostic_type' => 'full',
				),
			),
			array(
				'type'   => 'action',
				'id'     => 'send_email',
				'config' => array(
					'recipient'    => 'custom',
					'custom_email' => $config['email'],
					'subject'      => 'WPShadow Heavy Test Results',
					'message'      => "Your scheduled heavy diagnostic tests have completed.\n\nRun at: {{current_time}}\n\nPlease check your WPShadow dashboard for detailed results.",
				),
			),
		);

		$workflow_name = $config['is_temporary'] 
			? '[Auto] Heavy Tests - ' . current_time( 'Y-m-d H:i' )
			: 'Heavy Diagnostics - Nightly';

		$workflow = Workflow_Manager::save_workflow( $workflow_name, $blocks );

		// Mark as Kanban-generated for filtering
		self::mark_as_kanban_workflow( $workflow['id'], $config['is_temporary'] );

		return $workflow;
	}

	/**
	 * Create an autofix workflow (persistent or one-time)
	 *
	 * @param string $finding_id The finding/diagnostic ID
	 * @param array  $config Configuration options
	 * @return array Created workflow data
	 */
	public static function create_autofix_workflow( $finding_id, $config = array() ) {
		$defaults = array(
			'is_persistent' => false,
			'schedule_time' => null, // null = run ASAP
			'user_email'    => wp_get_current_user()->user_email,
		);

		$config = wp_parse_args( $config, $defaults );

		// Build the workflow blocks
		$blocks = array();

		if ( $config['schedule_time'] ) {
			// Time-based trigger for persistent workflows
			$blocks[] = array(
				'type'   => 'trigger',
				'id'     => 'time_trigger',
				'config' => array(
					'time' => $config['schedule_time'],
					'days' => array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ),
				),
			);
		} else {
			// Manual/immediate trigger (will be executed via cron ASAP)
			$blocks[] = array(
				'type'   => 'trigger',
				'id'     => 'manual_cron_trigger',
				'config' => array(
					'require_auth' => false,
					'allowed_ips'  => '',
				),
			);
		}

		// Check if the diagnostic needs fixing
		$blocks[] = array(
			'type'   => 'action',
			'id'     => 'run_diagnostic',
			'config' => array(
				'diagnostic_type'     => 'specific',
				'specific_diagnostic' => $finding_id,
			),
		);

		// Apply the treatment
		$blocks[] = array(
			'type'   => 'action',
			'id'     => 'apply_treatment',
			'config' => array(
				'specific_treatment' => $finding_id,
			),
		);

		// Send notification email
		$blocks[] = array(
			'type'   => 'action',
			'id'     => 'send_email',
			'config' => array(
				'recipient'    => 'custom',
				'custom_email' => $config['user_email'],
				'subject'      => 'WPShadow Auto-fix Applied: ' . $finding_id,
				'message'      => "An automatic fix has been applied to: {$finding_id}\n\nTime: {{current_time}}\n\nPlease verify the fix in your WPShadow dashboard.",
			),
		);

		$workflow_name = $config['is_persistent']
			? 'Auto-fix: ' . ucwords( str_replace( array( '-', '_' ), ' ', $finding_id ) )
			: '[Temp] Auto-fix: ' . $finding_id . ' - ' . current_time( 'Y-m-d H:i' );

		$workflow = Workflow_Manager::save_workflow( $workflow_name, $blocks );

		// Mark as Kanban-generated
		self::mark_as_kanban_workflow( $workflow['id'], ! $config['is_persistent'] );

		// If it's a one-time workflow, schedule it to run immediately
		if ( ! $config['is_persistent'] && ! $config['schedule_time'] ) {
			self::schedule_immediate_execution( $workflow );
		}

		return $workflow;
	}

	/**
	 * Mark a workflow as generated from Kanban
	 *
	 * @param string $workflow_id Workflow ID
	 * @param bool   $is_temporary Whether this is a temporary workflow
	 */
	private static function mark_as_kanban_workflow( $workflow_id, $is_temporary = false ) {
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		$kanban_workflows[ $workflow_id ] = array(
			'created_at'   => current_time( 'timestamp' ),
			'is_temporary' => $is_temporary,
		);
		update_option( 'wpshadow_kanban_workflows', $kanban_workflows );
	}

	/**
	 * Schedule immediate execution of a workflow
	 *
	 * @param array $workflow Workflow data
	 */
	private static function schedule_immediate_execution( $workflow ) {
		// Get the manual trigger token
		$token = null;
		foreach ( $workflow['blocks'] as $block ) {
			if ( $block['type'] === 'trigger' && $block['id'] === 'manual_cron_trigger' ) {
				$token = isset( $block['config']['trigger_token'] ) ? $block['config']['trigger_token'] : null;
				break;
			}
		}

		if ( ! $token ) {
			return;
		}

		// Schedule a single cron event to execute this workflow ASAP (in 30 seconds)
		wp_schedule_single_event(
			time() + 30,
			'wpshadow_execute_immediate_workflow',
			array( $workflow['id'], $token )
		);
	}

	/**
	 * Check if a workflow is managed by Kanban
	 *
	 * @param string $workflow_id Workflow ID
	 * @return bool
	 */
	public static function is_kanban_workflow( $workflow_id ) {
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		return isset( $kanban_workflows[ $workflow_id ] );
	}

	/**
	 * Check if a workflow is temporary
	 *
	 * @param string $workflow_id Workflow ID
	 * @return bool
	 */
	public static function is_temporary_workflow( $workflow_id ) {
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		return isset( $kanban_workflows[ $workflow_id ] ) && $kanban_workflows[ $workflow_id ]['is_temporary'];
	}

	/**
	 * Get workflows that should be hidden from the workflow list
	 *
	 * @return array Array of workflow IDs
	 */
	public static function get_hidden_workflow_ids() {
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		$hidden = array();

		foreach ( $kanban_workflows as $workflow_id => $data ) {
			if ( $data['is_temporary'] ) {
				$hidden[] = $workflow_id;
			}
		}

		return $hidden;
	}

	/**
	 * Clean up temporary workflows after execution
	 *
	 * @param string $workflow_id Workflow ID
	 */
	public static function cleanup_temporary_workflow( $workflow_id ) {
		if ( ! self::is_temporary_workflow( $workflow_id ) ) {
			return;
		}

		// Delete the workflow
		Workflow_Manager::delete_workflow( $workflow_id );

		// Remove from tracking
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		unset( $kanban_workflows[ $workflow_id ] );
		update_option( 'wpshadow_kanban_workflows', $kanban_workflows );
	}

	/**
	 * Clean up old temporary workflows (older than 24 hours)
	 */
	public static function cleanup_old_temporary_workflows() {
		$kanban_workflows = get_option( 'wpshadow_kanban_workflows', array() );
		$cutoff = current_time( 'timestamp' ) - DAY_IN_SECONDS;

		foreach ( $kanban_workflows as $workflow_id => $data ) {
			if ( $data['is_temporary'] && $data['created_at'] < $cutoff ) {
				Workflow_Manager::delete_workflow( $workflow_id );
				unset( $kanban_workflows[ $workflow_id ] );
			}
		}

		update_option( 'wpshadow_kanban_workflows', $kanban_workflows );
	}

	/**
	 * Check if a persistent autofix workflow exists for a finding
	 *
	 * @param string $finding_id Finding ID
	 * @return string|false Workflow ID or false
	 */
	public static function get_persistent_autofix_workflow( $finding_id ) {
		$workflows = Workflow_Manager::get_workflows();

		foreach ( $workflows as $workflow ) {
			if ( ! self::is_kanban_workflow( $workflow['id'] ) ) {
				continue;
			}

			if ( self::is_temporary_workflow( $workflow['id'] ) ) {
				continue;
			}

			// Check if this workflow applies the treatment for this finding
			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] === 'action' && $block['id'] === 'apply_treatment' ) {
					if ( isset( $block['config']['specific_treatment'] ) && $block['config']['specific_treatment'] === $finding_id ) {
						return $workflow['id'];
					}
				}
			}
		}

		return false;
	}
}
