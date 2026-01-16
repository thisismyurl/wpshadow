<?php
/**
 * Scheduled Tasks AJAX Handler
 *
 * Handles AJAX requests for managing scheduled cron tasks.
 *
 * @package    WP_Support
 * @subpackage Admin
 * @since      1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Scheduled_Tasks_Ajax Class
 *
 * Manages AJAX operations for pausing, resuming, and removing scheduled tasks.
 */
class WPSHADOW_Scheduled_Tasks_Ajax {

	/**
	 * Initialize the scheduled tasks AJAX handler.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_ajax_wpshadow_pause_task', array( __CLASS__, 'handle_pause_task' ) );
		add_action( 'wp_ajax_wpshadow_resume_task', array( __CLASS__, 'handle_resume_task' ) );
		add_action( 'wp_ajax_wpshadow_remove_task', array( __CLASS__, 'handle_remove_task' ) );
		add_action( 'wp_ajax_wpshadow_remove_paused_task', array( __CLASS__, 'handle_remove_paused_task' ) );
		
		// Enqueue scripts for scheduled tasks widget
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue JavaScript for scheduled tasks management.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_scripts( string $hook ): void {
		// Only load on WPShadow dashboard pages
		if ( strpos( $hook, 'wpshadow' ) === false ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-scheduled-tasks',
			WPSHADOW_URL . 'assets/js/scheduled-tasks.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-scheduled-tasks',
			'wpshadowScheduledTasks',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_scheduled_tasks' ),
				'strings' => array(
					'confirmRemove' => __( 'Remove this scheduled task? You\'ll need to set it up again if you change your mind.', 'plugin-wpshadow' ),
					'confirmPause'  => __( 'Pause this scheduled task? It will not run until you resume it.', 'plugin-wpshadow' ),
					'confirmDelete' => __( 'Delete this paused task? You\'ll need to set it up again if you change your mind.', 'plugin-wpshadow' ),
					'error'         => __( 'Something didn\'t work. Let\'s try again.', 'plugin-wpshadow' ),
					'success'       => __( 'Task updated successfully.', 'plugin-wpshadow' ),
				),
			)
		);
	}

	/**
	 * Handle AJAX request to pause a scheduled task.
	 *
	 * @return void
	 */
	public static function handle_pause_task(): void {
		check_ajax_referer( 'wpshadow_scheduled_tasks', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
		$timestamp = isset( $_POST['timestamp'] ) ? absint( $_POST['timestamp'] ) : 0;

		if ( empty( $hook ) || empty( $timestamp ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid task data', 'plugin-wpshadow' ) ) );
		}

		// Get the task details before unscheduling
		$cron_array = _get_cron_array();
		$schedule = 'once';
		$args = array();

		if ( isset( $cron_array[ $timestamp ][ $hook ] ) ) {
			foreach ( $cron_array[ $timestamp ][ $hook ] as $job ) {
				$schedule = isset( $job['schedule'] ) ? $job['schedule'] : 'once';
				$args = isset( $job['args'] ) ? $job['args'] : array();
				break;
			}
		}

		// Unschedule the task
		$result = wp_unschedule_event( $timestamp, $hook, $args );

		if ( $result === false ) {
			wp_send_json_error( array( 'message' => __( 'Couldn\'t pause that task. Try again?', 'plugin-wpshadow' ) ) );
		}

		// Store paused task information
		$paused_tasks = get_option( 'wpshadow_paused_tasks', array() );
		$paused_tasks[ $hook ] = array(
			'schedule'   => $schedule,
			'args'       => $args,
			'paused_at'  => time(),
			'paused_by'  => get_current_user_id(),
		);
		update_option( 'wpshadow_paused_tasks', $paused_tasks );

		// Log the action
		self::log_task_action( $hook, 'paused', array(
			'schedule' => $schedule,
			'user_id'  => get_current_user_id(),
		) );

		wp_send_json_success( array( 
			'message' => __( 'Task paused successfully', 'plugin-wpshadow' ),
			'reload'  => true,
		) );
	}

	/**
	 * Handle AJAX request to resume a paused task.
	 *
	 * @return void
	 */
	public static function handle_resume_task(): void {
		check_ajax_referer( 'wpshadow_scheduled_tasks', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';

		if ( empty( $hook ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid task data', 'plugin-wpshadow' ) ) );
		}

		// Get paused task information
		$paused_tasks = get_option( 'wpshadow_paused_tasks', array() );

		if ( ! isset( $paused_tasks[ $hook ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Task not found in paused list', 'plugin-wpshadow' ) ) );
		}

		$task_data = $paused_tasks[ $hook ];

		// Check if task is already scheduled
		if ( wp_next_scheduled( $hook, $task_data['args'] ) ) {
			// Remove from paused list
			unset( $paused_tasks[ $hook ] );
			update_option( 'wpshadow_paused_tasks', $paused_tasks );
			
			wp_send_json_success( array( 
				'message' => __( 'Task is already scheduled', 'plugin-wpshadow' ),
				'reload'  => true,
			) );
		}

		// Reschedule the task
		$result = wp_schedule_event( time(), $task_data['schedule'], $hook, $task_data['args'] );

		if ( $result === false ) {
			wp_send_json_error( array( 'message' => __( 'Couldn\'t start that task again. Try again?', 'plugin-wpshadow' ) ) );
		}

		// Remove from paused list
		unset( $paused_tasks[ $hook ] );
		update_option( 'wpshadow_paused_tasks', $paused_tasks );

		// Log the action
		self::log_task_action( $hook, 'resumed', array(
			'schedule' => $task_data['schedule'],
			'user_id'  => get_current_user_id(),
		) );

		wp_send_json_success( array( 
			'message' => __( 'Task resumed successfully', 'plugin-wpshadow' ),
			'reload'  => true,
		) );
	}

	/**
	 * Handle AJAX request to remove a scheduled task.
	 *
	 * @return void
	 */
	public static function handle_remove_task(): void {
		check_ajax_referer( 'wpshadow_scheduled_tasks', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
		$timestamp = isset( $_POST['timestamp'] ) ? absint( $_POST['timestamp'] ) : 0;

		if ( empty( $hook ) || empty( $timestamp ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid task data', 'plugin-wpshadow' ) ) );
		}

		// Get task details for logging
		$cron_array = _get_cron_array();
		$args = array();

		if ( isset( $cron_array[ $timestamp ][ $hook ] ) ) {
			foreach ( $cron_array[ $timestamp ][ $hook ] as $job ) {
				$args = isset( $job['args'] ) ? $job['args'] : array();
				break;
			}
		}

		// Unschedule the task
		$result = wp_unschedule_event( $timestamp, $hook, $args );

		if ( $result === false ) {
			wp_send_json_error( array( 'message' => __( 'Couldn\'t remove that task. Try again?', 'plugin-wpshadow' ) ) );
		}

		// Log the action
		self::log_task_action( $hook, 'removed', array(
			'user_id' => get_current_user_id(),
		) );

		wp_send_json_success( array( 
			'message' => __( 'Task removed successfully', 'plugin-wpshadow' ),
			'reload'  => true,
		) );
	}

	/**
	 * Handle AJAX request to delete a paused task.
	 *
	 * @return void
	 */
	public static function handle_remove_paused_task(): void {
		check_ajax_referer( 'wpshadow_scheduled_tasks', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$hook = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';

		if ( empty( $hook ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid task data', 'plugin-wpshadow' ) ) );
		}

		// Get paused tasks
		$paused_tasks = get_option( 'wpshadow_paused_tasks', array() );

		if ( ! isset( $paused_tasks[ $hook ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Task not found in paused list', 'plugin-wpshadow' ) ) );
		}

		// Remove from paused list
		unset( $paused_tasks[ $hook ] );
		update_option( 'wpshadow_paused_tasks', $paused_tasks );

		// Log the action
		self::log_task_action( $hook, 'deleted', array(
			'user_id' => get_current_user_id(),
		) );

		wp_send_json_success( array( 
			'message' => __( 'Paused task deleted successfully', 'plugin-wpshadow' ),
			'reload'  => true,
		) );
	}

	/**
	 * Log a task action.
	 *
	 * @param string $hook   Task hook name.
	 * @param string $action Action performed (paused, resumed, removed, deleted).
	 * @param array  $data   Additional data.
	 * @return void
	 */
	private static function log_task_action( string $hook, string $action, array $data = array() ): void {
		$logs = get_option( 'wpshadow_task_logs', array() );

		// Limit to 100 most recent logs
		if ( count( $logs ) >= 100 ) {
			array_shift( $logs );
		}

		$logs[] = array(
			'hook'      => $hook,
			'action'    => $action,
			'timestamp' => time(),
			'data'      => $data,
		);

		update_option( 'wpshadow_task_logs', $logs );
	}

	/**
	 * Get task logs.
	 *
	 * @param int $limit Number of logs to retrieve.
	 * @return array Task logs.
	 */
	public static function get_task_logs( int $limit = 10 ): array {
		$logs = get_option( 'wpshadow_task_logs', array() );
		return array_slice( array_reverse( $logs ), 0, $limit );
	}

	/**
	 * Clear all task logs.
	 *
	 * @return void
	 */
	public static function clear_task_logs(): void {
		delete_option( 'wpshadow_task_logs' );
	}
}
