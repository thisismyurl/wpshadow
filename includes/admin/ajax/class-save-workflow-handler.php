<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Workflow\Workflow_Wizard;
use WPShadow\Workflow\Block_Registry;
use WPShadow\Core\Activity_Logger;

/**
 * AJAX Handler: Save Workflow
 *
 * Handles saving of workflows in both legacy and new wizard formats.
 * Action: wp_ajax_wpshadow_save_workflow
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Save_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_workflow', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle AJAX request to save workflow
	 */
	public static function handle(): void {
		// Security verification
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		// Get parameters
		$name        = self::get_post_param( 'name', 'text', '', false );
		$blocks      = self::get_post_param( 'blocks', 'json', [], false );
		$workflow_id = self::get_post_param( 'workflow_id', 'key', null, false );
		$workflow    = self::get_post_param( 'workflow', 'text', '', false );

		// Handle new wizard format (takes precedence)
		if ( ! empty( $workflow ) ) {
			self::handle_wizard_format( $workflow );
			return;
		}

		// Handle legacy block format
		if ( ! empty( $blocks ) ) {
			self::handle_block_format( $name, $blocks, $workflow_id );
			return;
		}

		self::send_error( 'No workflow data provided.' );
	}

	/**
	 * Handle new wizard format
	 *
	 * @param string $workflow_json Workflow JSON string
	 */
	private static function handle_wizard_format( string $workflow_json ): void {
		$wizard_data = json_decode( $workflow_json, true );

		if ( ! $wizard_data || ! is_array( $wizard_data ) ) {
			self::send_error( 'Invalid workflow data.' );
			return;
		}

		// Convert wizard format to executor format
		$workflow = Workflow_Wizard::convert_to_executor_format( $wizard_data );

		// Save workflow
		$workflows = Options_Manager::get_array( 'wpshadow_workflows', [] );

		// Generate silly name if empty
		if ( empty( $workflow['name'] ) ) {
			$workflow['name'] = Workflow_Manager::generate_silly_name();
		}

		$workflows[ $workflow['id'] ] = $workflow;
		update_option( 'wpshadow_workflows', $workflows );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'workflow_saved',
			sprintf( __( 'Workflow saved: "%s"', 'wpshadow' ), $workflow['name'] ),
			'workflows',
			array( 'workflow_id' => $workflow['id'], 'blocks_count' => count( $workflow['blocks'] ?? [] ) )
		);

		self::send_success( [
			'message'  => 'Workflow saved successfully.',
			'workflow' => $workflow,
		] );
	}

	/**
	 * Handle legacy block format
	 *
	 * @param string $name         Workflow name
	 * @param array  $blocks       Workflow blocks
	 * @param mixed  $workflow_id  Optional workflow ID for updates
	 */
	private static function handle_block_format( string $name, array $blocks, $workflow_id ): void {
		if ( empty( $blocks ) ) {
			self::send_error( 'Workflow must contain at least one block.' );
			return;
		}

		// Validate blocks
		foreach ( $blocks as $block ) {
			$result = Block_Registry::validate_block( $block );
			if ( ! $result['valid'] ) {
				self::send_error( 'Invalid block: ' . $result['error'] );
				return;
			}
		}

		$workflow = Workflow_Manager::save_workflow( $name, $blocks, $workflow_id );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'workflow_saved',
			sprintf( __( 'Workflow saved: "%s" with %d blocks', 'wpshadow' ), $workflow['name'], count( $blocks ) ),
			'workflows',
			array( 'workflow_id' => $workflow['id'], 'blocks_count' => count( $blocks ) )
		);

		self::send_success( [
			'message'  => 'Workflow saved successfully.',
			'workflow' => $workflow,
		] );
	}
}
