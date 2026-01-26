<?php
/**
 * AJAX Handlers for Exit Followup Management
 *
 * Handles AJAX requests for viewing and managing exit interview followups.
 * Multiple handlers grouped in one file for related functionality.
 *
 * phpcs:disable WordPress.Files.FileName.InvalidClassFileName
 * phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
 *
 * @since   1.2601.2148
 * @package WPShadow\Admin\AJAX
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Engagement\Exit_Followup_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Exit Followups Handler
 */
class Get_Exit_Followups_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request to get exit followups.
	 *
	 * @since 1.2601.2148
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_get_exit_followups', 'manage_options' );

		// Get filter parameters
		$status = self::get_post_param( 'status', 'text', '', false );
		$limit  = self::get_post_param( 'limit', 'int', 50, false );

		// Get due followups or all pending
		if ( 'due' === $status ) {
			$followups = Exit_Followup_Manager::get_due_followups();
		} else {
			// For now, get all pending followups
			// In future, could add pagination
			$followups = self::get_all_followups( $status, $limit );
		}

		// Get statistics
		$stats = Exit_Followup_Manager::get_statistics();

		self::send_success(
			array(
				'followups' => $followups,
				'stats'     => $stats,
			)
		);
	}

	/**
	 * Get all followups with optional status filter.
	 *
	 * @since  1.2601.2148
	 * @param  string $status Status filter.
	 * @param  int    $limit  Maximum number of results.
	 * @return array Array of followup records.
	 */
	private static function get_all_followups( $status, $limit ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		$query = "SELECT f.*, i.contact_email, i.user_id, i.exit_reason 
				FROM {$table} f
				INNER JOIN {$wpdb->prefix}wpshadow_exit_interviews i ON f.interview_id = i.id";

		$params = array();

		if ( ! empty( $status ) ) {
			$query   .= ' WHERE f.status = %s';
			$params[] = $status;
		}

		$query   .= ' ORDER BY f.scheduled_date DESC LIMIT %d';
		$params[] = $limit;

		$followups = $wpdb->get_results(
			$wpdb->prepare( $query, ...$params ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			ARRAY_A
		);

		// Decode JSON fields
		foreach ( $followups as &$followup ) {
			if ( ! empty( $followup['survey_questions'] ) ) {
				$followup['survey_questions'] = json_decode( $followup['survey_questions'], true );
			}
			if ( ! empty( $followup['survey_responses'] ) ) {
				$followup['survey_responses'] = json_decode( $followup['survey_responses'], true );
			}
		}

		return $followups;
	}
}

/**
 * Update Exit Followup Handler
 */
class Update_Exit_Followup_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request to update a followup.
	 *
	 * @since 1.2601.2148
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_update_exit_followup', 'manage_options' );

		// Get parameters
		$followup_id = self::get_post_param( 'followup_id', 'int', 0, true );
		$status      = self::get_post_param( 'status', 'text', '', false );
		$notes       = self::get_post_param( 'notes', 'text', '', false );

		$update_data = array();

		if ( ! empty( $notes ) ) {
			$update_data['notes'] = $notes;
		}

		// Update followup status
		$result = Exit_Followup_Manager::update_followup_status( $followup_id, $status, $update_data );

		if ( $result ) {
			self::send_success(
				array(
					'message' => __( 'Followup updated successfully', 'wpshadow' ),
				)
			);
		} else {
			self::send_error( __( 'Failed to update followup', 'wpshadow' ) );
		}
	}
}

/**
 * Cancel Exit Followups Handler
 */
class Cancel_Exit_Followups_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request to cancel followups for an interview.
	 *
	 * @since 1.2601.2148
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_cancel_exit_followups', 'manage_options' );

		// Get parameters
		$interview_id = self::get_post_param( 'interview_id', 'int', 0, true );

		// Cancel all pending followups
		$result = Exit_Followup_Manager::cancel_followups( $interview_id );

		if ( $result ) {
			self::send_success(
				array(
					'message' => __( 'Followups cancelled successfully', 'wpshadow' ),
				)
			);
		} else {
			self::send_error( __( 'Failed to cancel followups', 'wpshadow' ) );
		}
	}
}

// Register AJAX handlers
add_action( 'wp_ajax_wpshadow_get_exit_followups', array( 'WPShadow\Admin\AJAX\Get_Exit_Followups_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_update_exit_followup', array( 'WPShadow\Admin\AJAX\Update_Exit_Followup_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_cancel_exit_followups', array( 'WPShadow\Admin\AJAX\Cancel_Exit_Followups_Handler', 'handle' ) );
