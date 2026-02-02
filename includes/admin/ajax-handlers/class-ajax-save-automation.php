<?php
/**
 * AJAX Handler: Save Automation
 *
 * Handles saving a new or edited automation from the wizard.
 *
 * @package WPShadow
 * @subpackage Admin\AJAX
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Save_Automation Class
 *
 * Processes automation save requests from the wizard interface.
 */
class AJAX_Save_Automation extends AJAX_Handler_Base {

	/**
	 * Handle AJAX request
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_save_automation', 'manage_options' );

		// Get and sanitize parameters
		$automation_name = self::get_post_param( 'automation_name', 'text', '', true );
		$trigger_id = self::get_post_param( 'trigger_id', 'text', '', true );
		$action_id = self::get_post_param( 'action_id', 'text', '', true );
		$frequency = self::get_post_param( 'frequency', 'text', 'daily' );
		$time = self::get_post_param( 'time', 'text', '02:00' );
		$day = self::get_post_param( 'day', 'text', 'monday' );
		$use_offpeak = self::get_post_param( 'use_offpeak', 'text' );
		$email_recipients = self::get_post_param( 'email_recipients', 'text' );
		$email_subject = self::get_post_param( 'email_subject', 'text' );

		// Build automation data
		$automation_data = array(
			'name'        => $automation_name,
			'status'      => 'active',
			'created_at'  => current_time( 'mysql' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_default_timezone_type
			'trigger'     => array(
				'id'         => $trigger_id,
				'frequency'  => $frequency,
				'time'       => $time,
				'day'        => $day,
				'use_offpeak' => ! empty( $use_offpeak ),
			),
			'action'      => array(
				'id'           => $action_id,
				'recipients'   => ! empty( $email_recipients ) ? array_map( 'trim', explode( ',', $email_recipients ) ) : array(),
				'subject'      => $email_subject,
			),
		);

		// Save to database using Workflow Manager
		try {
			$workflow_id = \WPShadow\Workflow\Workflow_Manager::create_workflow( $automation_data );

			if ( $workflow_id ) {
				self::send_success( array(
					'message'     => __( 'Automation saved successfully!', 'wpshadow' ),
					'workflow_id' => $workflow_id,
				) );
			} else {
				self::send_error( __( 'Failed to save automation.', 'wpshadow' ) );
			}
		} catch ( \Exception $e ) {
			self::send_error( sprintf( __( 'Error saving automation: %s', 'wpshadow' ), $e->getMessage() ) );
		}
	}
}

// Register AJAX handler
add_action( 'wp_ajax_wpshadow_save_automation', array( 'WPShadow\Admin\AJAX\AJAX_Save_Automation', 'handle' ) );
