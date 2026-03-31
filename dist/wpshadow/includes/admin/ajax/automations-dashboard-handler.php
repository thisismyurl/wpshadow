<?php
/**
 * AJAX Handler: Automation Dashboard Actions
 *
 * Handles AJAX requests for the automation dashboard:
 * activity retrieval, execution, deletion, and toggling.
 *
 * @since      1.6093.1200
 * @package    WPShadow
 * @subpackage Admin\Ajax
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automations_Dashboard_Handler Class
 *
 * Centralised handler for all AJAX actions on the automations dashboard.
 * Consolidates activity retrieval, workflow execution, deletion, and
 * enable/disable toggling into a single class-based handler.
 *
 * @since 1.6093.1200
 */
class Automations_Dashboard_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hooks.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_automation_activity', array( __CLASS__, 'handle_get_activity' ) );
		add_action( 'wp_ajax_wpshadow_run_automation',          array( __CLASS__, 'handle_run' ) );
		add_action( 'wp_ajax_wpshadow_delete_automation',       array( __CLASS__, 'handle_delete' ) );
		add_action( 'wp_ajax_wpshadow_toggle_automation',       array( __CLASS__, 'handle_toggle' ) );
	}

	/**
	 * Handle AJAX request to get automation activity history.
	 *
	 * @since  1.6093.1200
	 * @return void Dies with JSON response.
	 */
	public static function handle_get_activity(): void {
		self::verify_request( 'wpshadow_automations', 'read' );

		$workflow_id = self::get_post_param( 'workflow_id', 'text', '', true );

		if ( empty( $workflow_id ) ) {
			self::send_error( __( 'Missing workflow ID', 'wpshadow' ) );
		}

		$activity = \WPShadow\Core\Activity_Logger::get_activity(
			array(
				'type'       => 'workflow_executed',
				'meta_key'   => 'workflow_id',
				'meta_value' => $workflow_id,
				'limit'      => 10,
				'orderby'    => 'timestamp',
				'order'      => 'DESC',
			)
		);

		if ( ! is_array( $activity ) ) {
			$activity = array();
		}

		$formatted = array_map(
			static function ( $item ) {
				return array(
					'timestamp' => isset( $item['timestamp'] ) ? $item['timestamp'] : time(),
					'message'   => isset( $item['message'] ) ? $item['message'] : __( 'Workflow executed', 'wpshadow' ),
				);
			},
			$activity
		);

		self::send_success( $formatted );
	}

	/**
	 * Handle AJAX request to run an automation.
	 *
	 * @since  1.6093.1200
	 * @return void Dies with JSON response.
	 */
	public static function handle_run(): void {
		self::verify_request( 'wpshadow_automations' );

		$workflow_id = self::get_post_param( 'workflow_id', 'text', '', true );

		if ( empty( $workflow_id ) ) {
			self::send_error( __( 'Missing workflow ID', 'wpshadow' ) );
		}

		$workflow = \WPShadow\Workflow\Workflow_Manager::get_workflow( $workflow_id );

		if ( ! $workflow ) {
			self::send_error( __( 'Workflow not found', 'wpshadow' ) );
		}

		try {
			$result = \WPShadow\Workflow\Workflow_Manager::execute_workflow( $workflow_id );

			\WPShadow\Core\Activity_Logger::log(
				'workflow_executed',
				array(
					'workflow_id'   => $workflow_id,
					'workflow_name' => $workflow['name'] ?? 'Unknown',
					'result'        => $result,
				)
			);

			self::send_success(
				array(
					'message' => __( 'Automation executed successfully', 'wpshadow' ),
					'result'  => $result,
				)
			);
		} catch ( \Exception $e ) {
			self::send_error( __( 'Failed to execute automation', 'wpshadow' ) );
		}
	}

	/**
	 * Handle AJAX request to delete an automation.
	 *
	 * @since  1.6093.1200
	 * @return void Dies with JSON response.
	 */
	public static function handle_delete(): void {
		self::verify_request( 'wpshadow_automations' );

		$workflow_id = self::get_post_param( 'workflow_id', 'text', '', true );

		if ( empty( $workflow_id ) ) {
			self::send_error( __( 'Missing workflow ID', 'wpshadow' ) );
		}

		try {
			$result = \WPShadow\Workflow\Workflow_Manager::delete_workflow( $workflow_id );

			if ( $result ) {
				\WPShadow\Core\Activity_Logger::log(
					'workflow_deleted',
					array(
						'workflow_id' => $workflow_id,
					)
				);

				self::send_success( array( 'message' => __( 'Automation deleted successfully', 'wpshadow' ) ) );
			} else {
				self::send_error( __( 'Failed to delete automation', 'wpshadow' ) );
			}
		} catch ( \Exception $e ) {
			self::send_error( __( 'Failed to delete automation', 'wpshadow' ) );
		}
	}

	/**
	 * Handle AJAX request to toggle an automation enabled/disabled.
	 *
	 * @since  1.6093.1200
	 * @return void Dies with JSON response.
	 */
	public static function handle_toggle(): void {
		self::verify_request( 'wpshadow_automations' );

		$workflow_id = self::get_post_param( 'workflow_id', 'text', '', true );
		$enabled     = rest_sanitize_boolean( self::get_post_param( 'enabled', 'text', '' ) );

		if ( empty( $workflow_id ) ) {
			self::send_error( __( 'Missing workflow ID', 'wpshadow' ) );
		}

		$workflow = \WPShadow\Workflow\Workflow_Manager::get_workflow( $workflow_id );

		if ( ! $workflow ) {
			self::send_error( __( 'Workflow not found', 'wpshadow' ) );
		}

		$workflow['enabled'] = $enabled;

		try {
			$result = \WPShadow\Workflow\Workflow_Manager::save_workflow( $workflow_id, $workflow );

			if ( $result ) {
				$action = $enabled ? 'workflow_enabled' : 'workflow_disabled';

				\WPShadow\Core\Activity_Logger::log(
					$action,
					array(
						'workflow_id'   => $workflow_id,
						'workflow_name' => $workflow['name'] ?? 'Unknown',
					)
				);

				self::send_success(
					array(
						'message' => $enabled
							? __( 'Automation enabled', 'wpshadow' )
							: __( 'Automation disabled', 'wpshadow' ),
					)
				);
			} else {
				self::send_error( __( 'Failed to update automation status', 'wpshadow' ) );
			}
		} catch ( \Exception $e ) {
			self::send_error( __( 'Failed to update automation status', 'wpshadow' ) );
		}
	}
}

// Register all handlers.
Automations_Dashboard_Handler::register();
