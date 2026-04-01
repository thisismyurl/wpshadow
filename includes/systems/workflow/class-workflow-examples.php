<?php
/**
 * Workflow Examples - Pre-built example workflows
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages workflow examples and usage tracking
 */
class Workflow_Examples {

	const USED_EXAMPLES_OPTION = 'wpshadow_used_workflow_examples';
	const EXAMPLES_PER_PAGE    = 3;

	/**
	 * Get all available workflow examples
	 *
	 * @return array Array of example workflows
	 */
	public static function get_all_examples() {
		return array(
			'daily_health_check' => array(
				'name'        => __( 'Daily Health Check', 'wpshadow' ),
				'description' => __( 'Daily during off-peak hours: run diagnostics and email results', 'wpshadow' ),
				'icon'        => 'heart',
				'trigger'     => array(
					'type'   => 'time_trigger',
					'config' => array(
						'frequency'     => 'daily',
						'downtime_mode' => true,
						'time'          => '02:00',
						'trigger_id'    => 'time_daily',
					),
				),
				'actions'     => array(
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'memory_limit',
						),
					),
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'ssl',
						),
					),
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'outdated_plugins',
						),
					),
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'backup',
						),
					),
				),
			),
			'security_alert'     => array(
				'name'        => __( 'Security Alert', 'wpshadow' ),
				'description' => __( 'When plugins are activated, scan security and run diagnostics', 'wpshadow' ),
				'icon'        => 'shield',
				'trigger'     => array(
					'type'   => 'event_trigger',
					'config' => array(
						'event_type'    => 'plugin_state_changed',
						'plugin_action' => 'activated',
						'trigger_id'    => 'plugin_state_changed',
					),
				),
				'actions'     => array(
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'outdated_plugins',
						),
					),
					array(
						'id'     => 'run_diagnostic',
						'config' => array(
							'diagnostic_id' => 'debug_mode',
						),
					),
					array(
						'id'     => 'kanban_note',
						'config' => array(
							'title'       => 'Plugin Activated: Security Check Run',
							'description' => 'Security diagnostics initiated for newly activated plugin',
							'severity'    => 'high',
							'status'      => 'detected',
							'category'    => 'security',
						),
					),
				),
			),
			'ssl_monitor'        => array(
				'name'        => __( 'SSL Certificate Monitor', 'wpshadow' ),
				'description' => __( 'Daily during downtime: monitor SSL certificate and alert if expiring soon', 'wpshadow' ),
				'icon'        => 'lock',
				'trigger'     => array(
					'type'   => 'time_trigger',
					'config' => array(
						'frequency'  => 'daily',
						'time'       => '03:00',
						'trigger_id' => 'time_daily',
					),
				),
				'actions'     => array(
					array(
						'id'     => 'kanban_note',
						'config' => array(
							'title'       => 'SSL Certificate Check Complete',
							'description' => 'Daily SSL certificate monitoring completed - review for expiration warnings',
							'severity'    => 'critical',
							'status'      => 'detected',
							'category'    => 'security',
						),
					),
				),
			),
		);
	}

	/**
	 * Get featured workflow examples for quick-start cards.
	 *
	 * @since 0.6093.1200
	 * @return array Featured example definitions keyed by example slug.
	 */
	public static function get_featured_examples() {
		$all_examples  = self::get_all_examples();
		$featured_keys = array( 'daily_health_check', 'security_alert', 'ssl_monitor' );
		$featured      = array();

		foreach ( $featured_keys as $key ) {
			if ( isset( $all_examples[ $key ] ) ) {
				$featured[ $key ] = $all_examples[ $key ];
			}
		}

		return $featured;
	}

	/**
	 * Get available examples (not yet used)
	 *
	 * @return array Available examples
	 */
	public static function get_available_examples() {
		$all_examples = self::get_all_examples();
		$used         = self::get_used_examples();

		return array_diff_key( $all_examples, array_flip( $used ) );
	}

	/**
	 * Get examples to display - Always returns all 3 featured examples
	 * Featured examples rotate but are always available
	 *
	 * @return array Examples to display (always 3)
	 */
	public static function get_display_examples() {
		$all_examples = self::get_all_examples();

		// Featured examples (all 3 are featured for Quick Start)
		$featured_keys = array( 'daily_health_check', 'security_alert', 'ssl_monitor' );
		$display       = array();

		// Always show all featured examples
		foreach ( $featured_keys as $key ) {
			if ( isset( $all_examples[ $key ] ) ) {
				$display[ $key ] = $all_examples[ $key ];
			}
		}

		return $display;
	}

	/**
	 * Get list of used example keys
	 *
	 * @return array Used example keys
	 */
	public static function get_used_examples() {
		$used = get_option( self::USED_EXAMPLES_OPTION, array() );
		return is_array( $used ) ? $used : array();
	}

	/**
	 * Mark an example as used
	 *
	 * @param string $example_key Example key
	 */
	public static function mark_example_used( $example_key ) {
		$used = self::get_used_examples();

		if ( ! in_array( $example_key, $used, true ) ) {
			$used[] = $example_key;
			update_option( self::USED_EXAMPLES_OPTION, $used );
		}
	}

	/**
	 * Reset used examples (for testing)
	 */
	public static function reset_used_examples() {
		delete_option( self::USED_EXAMPLES_OPTION );
	}

	/**
	 * Get example data by key
	 *
	 * @param string $example_key Example key
	 * @return array|null Example data or null if not found
	 */
	public static function get_example( $example_key ) {
		$examples = self::get_all_examples();
		return isset( $examples[ $example_key ] ) ? $examples[ $example_key ] : null;
	}

	/**
	 * Create a workflow from an example
	 *
	 * @param string $example_key Example key
	 * @return array Created workflow
	 */
	public static function create_from_example( $example_key ) {
		$example = self::get_example( $example_key );

		if ( ! $example ) {
			return array( 'error' => __( 'Example not found', 'wpshadow' ) );
		}

		// Build blocks from example data
		$blocks = array();

		// Add trigger block
		if ( ! empty( $example['trigger'] ) ) {
			$trigger  = $example['trigger'];
			$blocks[] = array(
				'id'     => $trigger['config']['trigger_id'] ?? 'time_daily',
				'type'   => 'trigger',
				'config' => $trigger['config'],
			);
		}

		// Add action blocks
		if ( ! empty( $example['actions'] ) ) {
			foreach ( $example['actions'] as $action ) {
				$blocks[] = array(
					'id'     => $action['id'],
					'type'   => 'action',
					'config' => $action['config'],
				);
			}
		}

		// Save workflow with blocks
		$workflow_id = 'wf_' . wp_generate_uuid4();
		$saved       = Workflow_Manager::save_workflow( $example['name'], $blocks, $workflow_id );

		// Mark example as used
		self::mark_example_used( $example_key );

		return $saved;
	}
}
