<?php
/**
 * Workflow Execution Engine - Evaluates triggers and executes actions
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Engine that evaluates triggers and executes workflow actions
 */
class Workflow_Executor {

	/**
	 * Initialize the executor (hook into WordPress)
	 */
	public static function init() {
		// Page load triggers
		add_action( 'wp', array( __CLASS__, 'handle_frontend_page_load' ), 1 );
		add_action( 'admin_init', array( __CLASS__, 'handle_admin_page_load' ), 1 );
		
		// Cron for time-based triggers
		add_action( 'wpshadow_workflow_cron', array( __CLASS__, 'handle_scheduled_triggers' ) );
		
		// Event triggers
		add_action( 'activated_plugin', array( __CLASS__, 'handle_plugin_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'handle_plugin_deactivated' ), 10, 2 );
		add_action( 'switch_theme', array( __CLASS__, 'handle_theme_changed' ), 10, 3 );
		add_action( 'user_register', array( __CLASS__, 'handle_user_registered' ) );
		add_action( 'publish_post', array( __CLASS__, 'handle_post_published' ) );
		add_action( 'delete_post', array( __CLASS__, 'handle_post_deleted' ) );
		add_action( 'comment_post', array( __CLASS__, 'handle_comment_posted' ) );
		
		// Schedule cron if not already scheduled
		if ( ! wp_next_scheduled( 'wpshadow_workflow_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_workflow_cron' );
		}
	}

	/**
	 * Handle frontend page load
	 */
	public static function handle_frontend_page_load() {
		if ( is_admin() ) {
			return;
		}

		$context = array(
			'trigger_type' => 'page_load',
			'context'      => 'frontend',
			'is_admin'     => false,
			'post_type'    => get_post_type(),
			'is_single'    => is_single(),
			'is_page'      => is_page(),
			'is_archive'   => is_archive(),
			'is_category'  => is_category(),
			'is_tag'       => is_tag(),
			'is_home'      => is_home(),
			'is_front_page' => is_front_page(),
		);

		self::execute_matching_workflows( 'page_load_trigger', $context );
	}

	/**
	 * Handle admin page load
	 */
	public static function handle_admin_page_load() {
		$context = array(
			'trigger_type' => 'page_load',
			'context'      => 'admin',
			'is_admin'     => true,
			'screen'       => function_exists( 'get_current_screen' ) ? get_current_screen() : null,
		);

		self::execute_matching_workflows( 'page_load_trigger', $context );
	}

	/**
	 * Handle scheduled time-based triggers
	 */
	public static function handle_scheduled_triggers() {
		$workflows = Workflow_Manager::get_workflows();

		foreach ( $workflows as $workflow ) {
			if ( ! $workflow['enabled'] ) {
				continue;
			}

			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] !== 'trigger' || $block['id'] !== 'time_trigger' ) {
					continue;
				}

				if ( self::should_execute_time_trigger( $block['config'] ) ) {
					self::execute_workflow( $workflow, array( 'trigger_type' => 'time' ) );
				}
			}
		}
	}

	/**
	 * Check if time trigger should execute now
	 */
	private static function should_execute_time_trigger( $config ) {
		$target_time = isset( $config['time'] ) ? $config['time'] : '02:00';
		$target_days = isset( $config['days'] ) ? $config['days'] : array();

		$current_time = current_time( 'H:i' );
		$current_day  = strtolower( current_time( 'l' ) );

		// Check if current day is in target days
		if ( ! in_array( $current_day, $target_days, true ) ) {
			return false;
		}

		// Check if current time matches (within 1 hour window)
		$target_hour = (int) substr( $target_time, 0, 2 );
		$current_hour = (int) current_time( 'H' );

		return abs( $current_hour - $target_hour ) < 1;
	}

	/**
	 * Handle plugin activated event
	 */
	public static function handle_plugin_activated( $plugin, $network_wide ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'plugin_activated',
			'plugin'       => $plugin,
			'network_wide' => $network_wide,
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle plugin deactivated event
	 */
	public static function handle_plugin_deactivated( $plugin, $network_wide ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'plugin_deactivated',
			'plugin'       => $plugin,
			'network_wide' => $network_wide,
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle theme changed event
	 */
	public static function handle_theme_changed( $new_name, $new_theme, $old_theme ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'theme_changed',
			'new_theme'    => $new_name,
			'old_theme'    => $old_theme->get( 'Name' ),
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle user registered event
	 */
	public static function handle_user_registered( $user_id ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'user_registered',
			'user_id'      => $user_id,
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle post published event
	 */
	public static function handle_post_published( $post_id ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'post_published',
			'post_id'      => $post_id,
			'post_type'    => get_post_type( $post_id ),
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle post deleted event
	 */
	public static function handle_post_deleted( $post_id ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'post_deleted',
			'post_id'      => $post_id,
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle comment posted event
	 */
	public static function handle_comment_posted( $comment_id ) {
		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'comment_posted',
			'comment_id'   => $comment_id,
		);

		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Execute workflows that match a specific trigger
	 */
	private static function execute_matching_workflows( $trigger_id, $context ) {
		$workflows = Workflow_Manager::get_workflows();

		foreach ( $workflows as $workflow ) {
			if ( ! $workflow['enabled'] ) {
				continue;
			}

			// Check if this workflow has a matching trigger
			$has_trigger = false;
			$trigger_config = null;

			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] === 'trigger' && $block['id'] === $trigger_id ) {
					$has_trigger = true;
					$trigger_config = isset( $block['config'] ) ? $block['config'] : array();
					break;
				}
			}

			if ( ! $has_trigger ) {
				continue;
			}

			// Check if trigger conditions match
			if ( ! self::trigger_matches_context( $trigger_id, $trigger_config, $context ) ) {
				continue;
			}

			// Execute the workflow
			self::execute_workflow( $workflow, $context );
		}
	}

	/**
	 * Check if trigger conditions match the current context
	 */
	private static function trigger_matches_context( $trigger_id, $config, $context ) {
		switch ( $trigger_id ) {
			case 'page_load_trigger':
				return self::page_load_matches( $config, $context );

			case 'event_trigger':
				if ( ! isset( $config['event_type'], $context['event_type'] ) ) {
					return false;
				}
				return $config['event_type'] === $context['event_type'];

			case 'condition_trigger':
				return self::condition_matches( $config );

			default:
				return true;
		}
	}

	/**
	 * Check if page load trigger matches context
	 */
	private static function page_load_matches( $config, $context ) {
		$page_context = isset( $config['page_context'] ) ? $config['page_context'] : 'all';

		switch ( $page_context ) {
			case 'all':
				return true;

			case 'frontend':
				return ! $context['is_admin'];

			case 'admin':
				return $context['is_admin'];

			case 'frontend_pages':
				return ! $context['is_admin'] && $context['is_page'];

			case 'frontend_posts':
				return ! $context['is_admin'] && $context['is_single'] && $context['post_type'] === 'post';

			case 'frontend_single':
				return ! $context['is_admin'] && ( $context['is_single'] || $context['is_page'] );

			case 'frontend_archive':
				return ! $context['is_admin'] && $context['is_archive'];

			case 'frontend_category':
				return ! $context['is_admin'] && $context['is_category'];

			case 'frontend_home':
				return ! $context['is_admin'] && ( $context['is_home'] || $context['is_front_page'] );

			default:
				return false;
		}
	}

	/**
	 * Check if condition trigger matches
	 */
	private static function condition_matches( $config ) {
		$condition_type = isset( $config['condition_type'] ) ? $config['condition_type'] : '';
		$threshold = isset( $config['threshold'] ) ? (int) $config['threshold'] : 80;

		switch ( $condition_type ) {
			case 'memory_high':
				$limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
				$usage = memory_get_usage( true );
				$percentage = ( $usage / $limit ) * 100;
				return $percentage > $threshold;

			case 'memory_low':
				$limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
				$usage = memory_get_usage( true );
				$percentage = ( $usage / $limit ) * 100;
				return $percentage < $threshold;

			case 'plugins_outdated':
				$updates = get_site_transient( 'update_plugins' );
				$count = isset( $updates->response ) ? count( $updates->response ) : 0;
				return $count > 0;

			case 'debug_mode_enabled':
				return defined( 'WP_DEBUG' ) && WP_DEBUG;

			case 'ssl_invalid':
				return ! is_ssl();

			default:
				return false;
		}
	}

	/**
	 * Execute a complete workflow
	 */
	public static function execute_workflow( $workflow, $context = array() ) {
		$workflow_id = $workflow['id'];
		$results = array();

		// Log execution start
		self::log_execution( $workflow_id, 'started', $context );

		// Execute each action block
		foreach ( $workflow['blocks'] as $block ) {
			if ( $block['type'] !== 'action' ) {
				continue;
			}

			$result = self::execute_action( $block, $context );
			$results[] = array(
				'block_id' => $block['id'],
				'result'   => $result,
			);

			// Stop execution if action failed and should halt
			if ( ! $result['success'] && isset( $block['config']['halt_on_error'] ) && $block['config']['halt_on_error'] ) {
				break;
			}
		}

		// Log execution complete
		self::log_execution( $workflow_id, 'completed', array(
			'context' => $context,
			'results' => $results,
		) );

		return $results;
	}

	/**
	 * Execute a single action block
	 */
	private static function execute_action( $block, $context ) {
		$action_id = $block['id'];
		$config = isset( $block['config'] ) ? $block['config'] : array();

		switch ( $action_id ) {
			case 'run_diagnostic':
				return self::execute_diagnostic( $config, $context );

			case 'apply_treatment':
				return self::execute_treatment( $config, $context );

			case 'send_email':
				return self::execute_email( $config, $context );

			case 'log_action':
				return self::execute_log( $config, $context );

			case 'send_notification':
				return self::execute_notification( $config, $context );

			default:
				return array(
					'success' => false,
					'message' => 'Unknown action type: ' . $action_id,
				);
		}
	}

	/**
	 * Execute a diagnostic check
	 */
	private static function execute_diagnostic( $config, $context ) {
		$diagnostic_type = isset( $config['diagnostic_type'] ) ? $config['diagnostic_type'] : 'full';
		$specific_diagnostic = isset( $config['specific_diagnostic'] ) ? $config['specific_diagnostic'] : '';

		// If specific diagnostic is set, run just that one
		if ( ! empty( $specific_diagnostic ) ) {
			$class_name = self::get_diagnostic_class( $specific_diagnostic );
			
			if ( ! class_exists( $class_name ) ) {
				return array(
					'success' => false,
					'message' => 'Diagnostic class not found: ' . $specific_diagnostic,
				);
			}

			$result = call_user_func( array( $class_name, 'check' ) );
			
			return array(
				'success' => true,
				'message' => 'Diagnostic completed: ' . $specific_diagnostic,
				'finding' => $result,
			);
		}

		// Otherwise run the type of scan requested
		$findings = array();

		switch ( $diagnostic_type ) {
			case 'full':
				$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_all_checks();
				break;

			case 'memory':
				if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Memory_Limit' ) ) {
					$findings[] = \WPShadow\Diagnostics\Diagnostic_Memory_Limit::check();
				}
				break;

			case 'plugins':
				if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Outdated_Plugins' ) ) {
					$findings[] = \WPShadow\Diagnostics\Diagnostic_Outdated_Plugins::check();
				}
				if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Plugin_Count' ) ) {
					$findings[] = \WPShadow\Diagnostics\Diagnostic_Plugin_Count::check();
				}
				break;

			case 'ssl':
				if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_SSL' ) ) {
					$findings[] = \WPShadow\Diagnostics\Diagnostic_SSL::check();
				}
				break;
		}

		$findings = array_filter( $findings );

		return array(
			'success' => true,
			'message' => sprintf( 'Diagnostic scan completed. Found %d issues.', count( $findings ) ),
			'findings' => $findings,
			'count' => count( $findings ),
		);
	}

	/**
	 * Execute a treatment/fix
	 */
	private static function execute_treatment( $config, $context ) {
		$treatment_type = isset( $config['treatment_type'] ) ? $config['treatment_type'] : '';
		$specific_treatment = isset( $config['specific_treatment'] ) ? $config['specific_treatment'] : '';

		// If specific treatment is set, apply just that one
		if ( ! empty( $specific_treatment ) ) {
			$class_name = self::get_treatment_class( $specific_treatment );
			
			if ( ! class_exists( $class_name ) ) {
				return array(
					'success' => false,
					'message' => 'Treatment class not found: ' . $specific_treatment,
				);
			}

			if ( ! method_exists( $class_name, 'can_apply' ) || ! call_user_func( array( $class_name, 'can_apply' ) ) ) {
				return array(
					'success' => false,
					'message' => 'Treatment cannot be applied: ' . $specific_treatment,
				);
			}

			$result = call_user_func( array( $class_name, 'apply' ) );
			
			return $result;
		}

		return array(
			'success' => false,
			'message' => 'No specific treatment specified.',
		);
	}

	/**
	 * Send email notification
	 */
	private static function execute_email( $config, $context ) {
		$recipient = isset( $config['recipient'] ) ? $config['recipient'] : 'admin';
		$subject = isset( $config['subject'] ) ? $config['subject'] : 'WPShadow Workflow Notification';
		$message = isset( $config['message'] ) ? $config['message'] : '';

		$to = $recipient === 'admin' ? get_option( 'admin_email' ) : $config['custom_email'];

		if ( empty( $to ) ) {
			return array(
				'success' => false,
				'message' => 'No recipient email address.',
			);
		}

		// Replace variables in message
		$message = self::replace_variables( $message, $context );

		$sent = wp_mail( $to, $subject, $message );

		return array(
			'success' => $sent,
			'message' => $sent ? 'Email sent successfully.' : 'Failed to send email.',
		);
	}

	/**
	 * Log action to activity log
	 */
	private static function execute_log( $config, $context ) {
		$log_message = isset( $config['log_message'] ) ? $config['log_message'] : 'Workflow action executed';
		$log_message = self::replace_variables( $log_message, $context );

		$log = get_option( 'wpshadow_workflow_log', array() );
		$log[] = array(
			'message'   => $log_message,
			'context'   => $context,
			'timestamp' => current_time( 'timestamp' ),
		);

		// Keep only last 100 entries
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}

		update_option( 'wpshadow_workflow_log', $log );

		return array(
			'success' => true,
			'message' => 'Logged: ' . $log_message,
		);
	}

	/**
	 * Send in-app notification
	 */
	private static function execute_notification( $config, $context ) {
		$title = isset( $config['notification_title'] ) ? $config['notification_title'] : 'WPShadow Notification';
		$message = isset( $config['notification_message'] ) ? $config['notification_message'] : '';
		$type = isset( $config['notification_type'] ) ? $config['notification_type'] : 'info';

		$notifications = get_option( 'wpshadow_notifications', array() );
		$notifications[] = array(
			'title'     => $title,
			'message'   => self::replace_variables( $message, $context ),
			'type'      => $type,
			'timestamp' => current_time( 'timestamp' ),
			'read'      => false,
		);

		update_option( 'wpshadow_notifications', $notifications );

		return array(
			'success' => true,
			'message' => 'Notification created.',
		);
	}

	/**
	 * Get diagnostic class name from slug
	 */
	private static function get_diagnostic_class( $slug ) {
		$class_name = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $slug ) ) );
		return '\\WPShadow\\Diagnostics\\Diagnostic_' . $class_name;
	}

	/**
	 * Get treatment class name from slug
	 */
	private static function get_treatment_class( $slug ) {
		$class_name = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $slug ) ) );
		return '\\WPShadow\\Treatments\\Treatment_' . $class_name;
	}

	/**
	 * Replace variables in a string with context values
	 */
	private static function replace_variables( $string, $context ) {
		foreach ( $context as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$string = str_replace( '{' . $key . '}', $value, $string );
			}
		}

		return $string;
	}

	/**
	 * Log workflow execution
	 */
	private static function log_execution( $workflow_id, $status, $data = array() ) {
		$log = get_option( 'wpshadow_workflow_executions', array() );
		$log[] = array(
			'workflow_id' => $workflow_id,
			'status'      => $status,
			'data'        => $data,
			'timestamp'   => current_time( 'timestamp' ),
		);

		// Keep only last 500 entries
		if ( count( $log ) > 500 ) {
			$log = array_slice( $log, -500 );
		}

		update_option( 'wpshadow_workflow_executions', $log );
	}
}
