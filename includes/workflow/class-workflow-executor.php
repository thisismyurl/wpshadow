<?php

/**
 * Workflow Execution Engine - Evaluates triggers and executes actions
 *
 * Handles trigger detection and fires matching workflows with appropriate context.
 *
 * SECURITY NOTES:
 * - Manual/External CRON triggers use random 32-char hex tokens (128-bit entropy)
 * - Token validation uses hash_equals() to prevent timing attacks
 * - Tokens are auto-generated on workflow save, never expose workflow IDs
 * - Query string format: ?wpshadow_trigger=RANDOM_TOKEN_HERE
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-context-builder.php';
require_once __DIR__ . '/../utils/class-email-service.php';
require_once __DIR__ . '/../core/class-options-manager.php';

use WPShadow\Utils\Email_Service;
use WPShadow\Core\Options_Manager;

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
		add_action( 'transition_post_status', array( __CLASS__, 'handle_post_status_changed' ), 10, 3 );
		add_action( 'pre_post_update', array( __CLASS__, 'handle_pre_publish_review' ) );
		add_action( 'delete_post', array( __CLASS__, 'handle_post_deleted' ) );
		add_action( 'comment_post', array( __CLASS__, 'handle_comment_posted' ) );

		// Plugin/Theme update triggers
		add_action( 'load-update.php', array( __CLASS__, 'handle_update_check' ) );
		add_action( 'load-plugins.php', array( __CLASS__, 'handle_update_check' ) );
		add_action( 'load-themes.php', array( __CLASS__, 'handle_update_check' ) );

		// Backup completion triggers
		add_action( 'wpshadow_backup_completed', array( __CLASS__, 'handle_backup_completed' ), 10, 1 );

		// Database issue triggers
		add_action( 'wpshadow_database_check', array( __CLASS__, 'handle_database_check' ) );

		// Error log triggers
		add_action( 'wpshadow_error_log_entry', array( __CLASS__, 'handle_error_logged' ), 10, 2 );

		// Diagnostic run triggers (manual or Guardian)
		add_action( 'wpshadow_activity_logged', array( __CLASS__, 'handle_activity_logged' ), 10, 1 );

		// Manual/CRON trigger via query string
		add_action( 'wp', array( __CLASS__, 'handle_query_string_trigger' ), 1 );

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

		$context = Context_Builder::build_frontend_page_load();
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
	 * Handle scContext_Builder::build_admin_page_load();           if ( ! $workflow['enabled'] ) {
				continue;
			}

			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] !== 'trigger' || $block['id'] !== 'time_trigger' ) {
					continue;
				}

				if ( self::should_execute_time_trigger( $block['config'] ) ) {
					self::execute_workflow( $workflow, Context_Builder::build_time_trigger() );
				}
			}
		}
	}

	/**
	 * Check if time trigger should execute now
	 * Supports hourly, daily, and weekly frequencies (#572-573)
	 */
	private static function should_execute_time_trigger( $config ) {
		$frequency   = isset( $config['frequency'] ) ? $config['frequency'] : 'daily';
		$target_time = isset( $config['time'] ) ? $config['time'] : '02:00';
		$target_days = isset( $config['days'] ) ? $config['days'] : array();

		$current_time = current_time( 'H:i' );
		$current_day  = strtolower( current_time( 'l' ) );

		// Hourly: run every hour (ignore time/days)
		if ( 'hourly' === $frequency ) {
			// Check if we're at the top of the hour (within 5 minutes)
			$current_minute = (int) current_time( 'i' );
			return $current_minute < 5;
		}

		// Daily/Weekly: check day constraint
		if ( ! empty( $target_days ) && ! in_array( $current_day, $target_days, true ) ) {
			return false;
		}

		// Weekly: run only once per week (on specified day)
		if ( 'weekly' === $frequency ) {
			// Get the first target day (primary day for weekly)
			$primary_day = ! empty( $target_days ) ? $target_days[0] : 'monday';
			if ( $current_day !== $primary_day ) {
				return false;
			}
		}

		// Check if current time matches (within 1 hour window)
		$target_hour  = (int) substr( $target_time, 0, 2 );
		$current_hour = (int) current_time( 'H' );

		return abs( $current_hour - $target_hour ) < 1;
	}

	/**
	 * Handle plugin activated event
	 */
	public static function handle_plugin_activated( $plugin, $network_wide ) {
		$context = Context_Builder::build_plugin_state_changed( $plugin, 'activated' );
		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle plugin deactivated event
	 */
	public static function handle_plugin_deactivated( $plugin, $network_wide ) {
		$context = Context_Builder::build_plugin_state_changed( $plugin, 'deactivated' );
		self::execute_matching_workflows( 'event_trigger', $context );
	}

	/**
	 * Handle theme changed event
	 */
	public static function handle_theme_changed( $new_name, $new_theme, $old_theme ) {
		$context = Context_Builder::build_theme_switched( $new_name, $new_theme, $old_theme );
		self::execute_matching_workflows( 'theme_switched', $context );
	}

	/**
	 * Handle user registered event
	 */
	public static function handle_user_registered( $user_id ) {
		$context = Context_Builder::build_user_registered( $user_id );
		self::execute_matching_workflows( 'user_register', $context );
	}

	/**
	 * Handle post status changed event
	 */
	public static function handle_post_status_changed( $new_status, $old_status, $post ) {
		// Skip auto-draft and other intermediate statuses
		if ( in_array( $new_status, array( 'auto-draft', 'inherit' ), true ) ) {
			return;
		}

		$context = Context_Builder::build_post_status_changed( $new_status, $old_status, $post );
		self::execute_matching_workflows( 'post_status_changed', $context );
	}

	/**
	 * Handle pre-publish review trigger
	 * Fires before a post is saved/published, allowing for validation or blocking
	 *
	 * @param int $post_id Post ID
	 */
	public static function handle_pre_publish_review( $post_id ) {
		$post = get_post( $post_id );

		// Only trigger for actual post types, not auto-drafts
		if ( ! $post || in_array( $post->post_status, array( 'auto-draft', 'inherit' ), true ) ) {
			return;
		}

		$context = array(
			'trigger_type' => 'event',
			'event_type'   => 'pre_publish_review',
			'post_id'      => $post_id,
			'post_type'    => $post->post_type,
			'post_title'   => $post->post_title,
			'post_status'  => $post->post_status,
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
			$has_trigger    = false;
			$trigger_config = null;

			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] === 'trigger' && $block['id'] === $trigger_id ) {
					$has_trigger    = true;
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

				// First check if event type matches
				if ( $config['event_type'] !== $context['event_type'] ) {
					return false;
				}

				// For plugin state changes, check the plugin_action config
				if ( 'plugin_state_changed' === $context['event_type'] ) {
					$plugin_action = isset( $config['plugin_action'] ) ? $config['plugin_action'] : 'any';
					if ( 'any' !== $plugin_action && $plugin_action !== $context['plugin_action'] ) {
						return false;
					}
				}

				// For post status changes, check the post_status config
				if ( 'post_status_changed' === $context['event_type'] ) {
					$post_status = isset( $config['post_status'] ) ? $config['post_status'] : 'any';
					if ( 'any' !== $post_status && $post_status !== $context['post_status'] ) {
						return false;
					}
				}

				// For pre-publish review, match all pre-publish reviews
				if ( 'pre_publish_review' === $context['event_type'] ) {
					// Additional filtering can be added here for post types if needed
					return true;
				}

				return true;
			case 'plugin_update_trigger':
				return self::plugin_update_matches( $config, $context );

			case 'backup_completion_trigger':
				return self::backup_completion_matches( $config, $context );

			case 'database_trigger':
				return self::database_issue_matches( $config, $context );

			case 'error_log_trigger':
				return self::error_log_matches( $config, $context );

			case 'diagnostic_run_trigger':
				return self::diagnostic_run_matches( $config, $context );

			case 'manual_cron_trigger':
				return true; // Already validated in handle_query_string_trigger

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
		$threshold      = isset( $config['threshold'] ) ? (int) $config['threshold'] : 80;

		switch ( $condition_type ) {
			case 'memory_high':
				$limit      = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
				$usage      = memory_get_usage( true );
				$percentage = ( $usage / $limit ) * 100;
				return $percentage > $threshold;

			case 'memory_low':
				$limit      = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
				$usage      = memory_get_usage( true );
				$percentage = ( $usage / $limit ) * 100;
				return $percentage < $threshold;

			case 'plugins_outdated':
				$updates = get_site_transient( 'update_plugins' );
				$count   = isset( $updates->response ) ? count( $updates->response ) : 0;
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
	 * Check if plugin/theme update trigger matches
	 */
	private static function plugin_update_matches( $config, $context ) {
		if ( ! isset( $context['available_updates'] ) ) {
			return false;
		}

		$target_type   = isset( $config['target_type'] ) ? $config['target_type'] : 'any';
		$specific_slug = isset( $config['specific_slug'] ) ? $config['specific_slug'] : '';

		if ( 'any' === $target_type ) {
			return ! empty( $context['available_updates'] );
		}

		if ( 'specific' === $target_type && ! empty( $specific_slug ) ) {
			foreach ( $context['available_updates'] as $update ) {
				if ( $update['slug'] === $specific_slug ) {
					return true;
				}
			}
			return false;
		}

		// Filter by plugin or theme
		foreach ( $context['available_updates'] as $update ) {
			if ( $update['type'] === $target_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if backup completion trigger matches
	 */
	private static function backup_completion_matches( $config, $context ) {
		if ( ! isset( $context['backup_status'] ) ) {
			return false;
		}

		$status = isset( $config['backup_status'] ) ? $config['backup_status'] : 'any';

		if ( 'any' === $status ) {
			return true;
		}

		return $status === $context['backup_status'];
	}

	/**
	 * Check if database issue trigger matches
	 */
	private static function database_issue_matches( $config, $context ) {
		if ( ! isset( $context['issues'] ) || empty( $context['issues'] ) ) {
			return false;
		}

		$issue_type     = isset( $config['database_issue'] ) ? $config['database_issue'] : '';
		$size_threshold = isset( $config['size_mb'] ) ? (float) $config['size_mb'] : 500;

		foreach ( $context['issues'] as $issue ) {
			if ( 'size_threshold' === $issue_type && 'size' === $issue['type'] ) {
				if ( $issue['size'] >= $size_threshold ) {
					return true;
				}
			} elseif ( $issue_type === $issue['type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if error log trigger matches
	 */
	private static function error_log_matches( $config, $context ) {
		if ( ! isset( $context['error_level'] ) ) {
			return false;
		}

		$level = isset( $config['error_level'] ) ? $config['error_level'] : 'any';

		if ( 'any' === $level ) {
			return true;
		}

		$severity_order = array(
			'warning'  => 1,
			'error'    => 2,
			'critical' => 3,
		);
		$config_level   = isset( $severity_order[ $level ] ) ? $severity_order[ $level ] : 0;
		$context_level  = isset( $severity_order[ $context['error_level'] ] ) ? $severity_order[ $context['error_level'] ] : 0;

		return $context_level >= $config_level;
	}

	/**
	 * Check if diagnostic run trigger matches
	 */
	private static function diagnostic_run_matches( $config, $context ) {
		$source_filter = isset( $config['source'] ) ? $config['source'] : 'any';
		$specific      = isset( $config['specific_diagnostic'] ) ? trim( (string) $config['specific_diagnostic'] ) : '';
		$issues_only   = ! empty( $config['issues_only'] );

		if ( 'any' !== $source_filter ) {
			if ( empty( $context['source'] ) || $source_filter !== $context['source'] ) {
				return false;
			}
		}

		if ( '' !== $specific ) {
			if ( empty( $context['diagnostic'] ) || $context['diagnostic'] !== $specific ) {
				return false;
			}
		}

		if ( $issues_only && empty( $context['found_issue'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Execute a complete workflow
	 */
	public static function execute_workflow( $workflow, $context = array() ) {
		$workflow_id = $workflow['id'];
		$results     = array();

		// Log execution start
		self::log_execution( $workflow_id, 'started', $context );

		// Execute each action block
		foreach ( $workflow['blocks'] as $block ) {
			if ( $block['type'] !== 'action' ) {
				continue;
			}

			$result    = self::execute_action( $block, $context );
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
		self::log_execution(
			$workflow_id,
			'completed',
			array(
				'context' => $context,
				'results' => $results,
			)
		);

		return $results;
	}

	/**
	 * Execute a single action block
	 */
	private static function execute_action( $block, $context ) {
		$action_id = $block['id'];
		$config    = isset( $block['config'] ) ? $block['config'] : array();

		switch ( $action_id ) {
			case 'run_diagnostic':
				return self::execute_diagnostic( $config, $context );

			case 'run_tool':
				return self::execute_tool( $config, $context );

			case 'apply_treatment':
				return self::execute_treatment( $config, $context );

			case 'send_email':
				return self::execute_email( $config, $context );

			case 'log_action':
				return self::execute_log( $config, $context );

			case 'send_notification':
				return self::execute_notification( $config, $context );

			case 'kanban_note':
				return self::execute_kanban_note( $config, $context );

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
		$diagnostic_type     = isset( $config['diagnostic_type'] ) ? $config['diagnostic_type'] : 'full';
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
				$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_deepscan_checks();
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
			'success'  => true,
			'message'  => sprintf( 'Diagnostic scan completed. Found %d issues.', count( $findings ) ),
			'findings' => $findings,
			'count'    => count( $findings ),
		);
	}

	/**
	 * Execute a treatment/fix
	 */
	private static function execute_treatment( $config, $context ) {
		$treatment_type     = isset( $config['treatment_type'] ) ? $config['treatment_type'] : '';
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
		// Load the email recipient manager
		require_once __DIR__ . '/class-email-recipient-manager.php';

		$recipient = isset( $config['recipient'] ) ? sanitize_text_field( $config['recipient'] ) : 'admin';
		$subject   = isset( $config['subject'] ) ? sanitize_text_field( $config['subject'] ) : 'WPShadow Workflow Notification';
		$message   = isset( $config['message'] ) ? $config['message'] : '';

		// Determine the actual email address
		if ( 'admin' === $recipient ) {
			$to = get_option( 'admin_email' );
		} else {
			// Verify the email is in the approved list
			if ( ! Email_Recipient_Manager::is_approved( $recipient ) ) {
				return array(
					'success' => false,
					'message' => 'Email recipient is not approved: ' . sanitize_email( $recipient ),
				);
			}
			$to = sanitize_email( $recipient );
		}

		if ( empty( $to ) ) {
			return array(
				'success' => false,
				'message' => 'No valid recipient email address.',
			);
		}

		// Replace variables in message
		$message = self::replace_variables( $message, $context );

		$result = Email_Service::send( $to, $subject, $message, array(), array() );

		return $result;
	}

	/**
	 * Log action to activity log (using transients for temporary storage)
	 */
	private static function execute_log( $config, $context ) {
		$log_message = isset( $config['log_message'] ) ? $config['log_message'] : 'Workflow action executed';
		$log_message = self::replace_variables( $log_message, $context );

		$log   = Options_Manager::get_array( 'wpshadow_workflow_log', array() );
		$log[] = array(
			'message'   => $log_message,
			'context'   => $context,
			'timestamp' => current_time( 'timestamp' ),
		);

		// Keep only last 100 entries
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}

		// Use transients for 24 hours (temporary data)
		Options_Manager::set( 'wpshadow_workflow_log', $log, true, DAY_IN_SECONDS, false );

		return array(
			'success' => true,
			'message' => 'Logged: ' . $log_message,
		);
	}

	/**
	 * Execute a WPShadow tool action.
	 */
	private static function execute_tool( $config, $context ) {
		$tool = isset( $config['tool'] ) ? $config['tool'] : '';

		switch ( $tool ) {
			case 'a11y-audit':
				return self::execute_a11y_audit( $config, $context );

			case 'broken-links':
				return self::execute_broken_links( $config, $context );

			case 'mobile-friendliness':
				return self::execute_mobile_friendliness( $config, $context );

			case 'simple-cache':
				return self::execute_simple_cache( $config, $context );

			case 'magic-link-support':
				return self::execute_magic_link_support( $config, $context );

			case 'dark-mode':
				$pref = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );
				return array(
					'success'    => true,
					'message'    => 'Dark mode preference read',
					'preference' => $pref,
				);

			default:
				return array(
					'success' => false,
					'message' => 'Tool not supported for automation: ' . $tool,
				);
		}
	}

	/**
	 * Execute accessibility audit tool
	 */
	private static function execute_a11y_audit( $config, $context ) {
		$scan_mode = isset( $config['scan_mode'] ) ? $config['scan_mode'] : 'specific';
		$url       = isset( $config['url'] ) ? esc_url_raw( $config['url'] ) : home_url();

		if ( 'specific' === $scan_mode ) {
			// Scan a specific URL
			if ( ! wp_http_validate_url( $url ) ) {
				return array(
					'success' => false,
					'message' => 'Invalid URL provided.',
				);
			}

			// Validate same-site
			$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
			$url_host  = wp_parse_url( $url, PHP_URL_HOST );
			if ( $url_host !== $site_host ) {
				return array(
					'success' => false,
					'message' => 'Can only scan pages from your own site.',
				);
			}

			// Fetch and analyze
			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 10,
					'headers' => array( 'User-Agent' => 'WPShadow-Workflow-A11y' ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return array(
					'success' => false,
					'message' => 'Failed to fetch URL: ' . $response->get_error_message(),
				);
			}

			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				return array(
					'success' => false,
					'message' => 'Empty response from URL.',
				);
			}

			if ( function_exists( 'wpshadow_analyze_a11y_html' ) ) {
				$issues  = wpshadow_analyze_a11y_html( $body );
				$summary = array(
					'pass' => 0,
					'warn' => 0,
					'fail' => 0,
				);
				foreach ( $issues as $issue ) {
					$status = $issue['status'] ?? '';
					if ( isset( $summary[ $status ] ) ) {
						++$summary[ $status ];
					}
				}
				return array(
					'success' => true,
					'message' => 'Accessibility audit completed.',
					'url'     => $url,
					'summary' => $summary,
					'issues'  => $issues,
				);
			}
		} elseif ( 'cluster' === $scan_mode ) {
			// Scan a cluster of URLs
			$urls    = isset( $config['urls'] ) ? (array) $config['urls'] : array( home_url() );
			$results = array();

			foreach ( $urls as $page_url ) {
				$page_url = esc_url_raw( $page_url );
				if ( ! wp_http_validate_url( $page_url ) ) {
					continue;
				}

				$response = wp_remote_get(
					$page_url,
					array(
						'timeout' => 10,
						'headers' => array( 'User-Agent' => 'WPShadow-Workflow-A11y' ),
					)
				);

				if ( is_wp_error( $response ) ) {
					continue;
				}

				$body = wp_remote_retrieve_body( $response );
				if ( ! empty( $body ) && function_exists( 'wpshadow_analyze_a11y_html' ) ) {
					$issues  = wpshadow_analyze_a11y_html( $body );
					$summary = array(
						'pass' => 0,
						'warn' => 0,
						'fail' => 0,
					);
					foreach ( $issues as $issue ) {
						$status = $issue['status'] ?? '';
						if ( isset( $summary[ $status ] ) ) {
							++$summary[ $status ];
						}
					}
					$results[ $page_url ] = $summary;
				}
			}

			return array(
				'success'      => true,
				'message'      => 'Accessibility audit cluster scan completed.',
				'urls_scanned' => count( $results ),
				'results'      => $results,
			);
		} elseif ( 'all' === $scan_mode ) {
			// Scan all posts/pages in batches
			$paged    = isset( $config['batch_number'] ) ? (int) $config['batch_number'] : 1;
			$per_page = isset( $config['batch_size'] ) ? (int) $config['batch_size'] : 10;

			$posts = get_posts(
				array(
					'posts_per_page' => $per_page,
					'paged'          => $paged,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
				)
			);

			if ( empty( $posts ) ) {
				return array(
					'success'      => true,
					'message'      => 'No more posts to scan.',
					'batch_number' => $paged,
					'urls_scanned' => 0,
				);
			}

			$results = array();
			foreach ( $posts as $post ) {
				$post_url = get_permalink( $post->ID );
				$response = wp_remote_get(
					$post_url,
					array(
						'timeout' => 10,
						'headers' => array( 'User-Agent' => 'WPShadow-Workflow-A11y' ),
					)
				);

				if ( ! is_wp_error( $response ) ) {
					$body = wp_remote_retrieve_body( $response );
					if ( ! empty( $body ) && function_exists( 'wpshadow_analyze_a11y_html' ) ) {
						$issues  = wpshadow_analyze_a11y_html( $body );
						$summary = array(
							'pass' => 0,
							'warn' => 0,
							'fail' => 0,
						);
						foreach ( $issues as $issue ) {
							$status = $issue['status'] ?? '';
							if ( isset( $summary[ $status ] ) ) {
								++$summary[ $status ];
							}
						}
						$results[ $post_url ] = $summary;
					}
				}
			}

			return array(
				'success'      => true,
				'message'      => 'Batch ' . $paged . ' scanned.',
				'batch_number' => $paged,
				'urls_scanned' => count( $results ),
				'results'      => $results,
				'has_more'     => count( $posts ) >= $per_page,
			);
		}

		return array(
			'success' => false,
			'message' => 'Invalid scan mode.',
		);
	}

	/**
	 * Execute broken links tool
	 */
	private static function execute_broken_links( $config, $context ) {
		$scan_mode = isset( $config['scan_mode'] ) ? $config['scan_mode'] : 'all';

		if ( 'specific' === $scan_mode ) {
			$url = isset( $config['url'] ) ? esc_url_raw( $config['url'] ) : home_url();
			if ( ! wp_http_validate_url( $url ) ) {
				return array(
					'success' => false,
					'message' => 'Invalid URL provided.',
				);
			}
		}

		if ( function_exists( 'wpshadow_run_broken_links_scan' ) ) {
			$result = wpshadow_run_broken_links_scan(
				array(
					'check_internal' => true,
					'check_external' => true,
					'check_images'   => true,
					'scan_mode'      => $scan_mode,
					'url'            => isset( $config['url'] ) ? esc_url_raw( $config['url'] ) : null,
				)
			);
			return array(
				'success'   => true,
				'message'   => 'Broken links scan completed',
				'scan_mode' => $scan_mode,
				'data'      => $result,
			);
		}

		return array(
			'success' => false,
			'message' => 'Tool handler unavailable.',
		);
	}

	/**
	 * Execute mobile friendliness tool
	 */
	private static function execute_mobile_friendliness( $config, $context ) {
		$scan_mode = isset( $config['scan_mode'] ) ? $config['scan_mode'] : 'specific';
		$url       = isset( $config['url'] ) ? esc_url_raw( $config['url'] ) : home_url();

		if ( 'specific' === $scan_mode ) {
			if ( ! wp_http_validate_url( $url ) ) {
				return array(
					'success' => false,
					'message' => 'Invalid URL provided.',
				);
			}

			// Validate same-site
			$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
			$url_host  = wp_parse_url( $url, PHP_URL_HOST );
			if ( $url_host !== $site_host ) {
				return array(
					'success' => false,
					'message' => 'Can only scan pages from your own site.',
				);
			}
		}

		if ( function_exists( 'wpshadow_run_mobile_friendliness' ) ) {
			$result = wpshadow_run_mobile_friendliness( $url, $scan_mode );
			return array(
				'success'   => true,
				'message'   => 'Mobile friendliness scan completed',
				'scan_mode' => $scan_mode,
				'findings'  => $result,
			);
		}

		return array(
			'success' => false,
			'message' => 'Tool handler unavailable.',
		);
	}

	/**
	 * Execute simple cache tool
	 */
	private static function execute_simple_cache( $config, $context ) {
		$action = isset( $config['action'] ) ? $config['action'] : 'status';

		if ( 'clear' === $action ) {
			// Clear cache directory
			$cache_dir = WP_CONTENT_DIR . '/wpshadow-cache';
			if ( file_exists( $cache_dir ) ) {
				array_map( 'unlink', glob( "$cache_dir/*.*" ) );
				rmdir( $cache_dir );
			}

			// Flush object cache
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}

			return array(
				'success' => true,
				'message' => 'Cache cleared successfully.',
			);
		}

		if ( 'save_options' === $action ) {
			// Save cache options
			$options = isset( $config['options'] ) ? (array) $config['options'] : array();
			foreach ( $options as $option => $value ) {
				update_option( 'wpshadow_cache_' . $option, $value );
			}

			return array(
				'success' => true,
				'message' => 'Cache options saved.',
				'options' => $options,
			);
		}

		return array(
			'success' => true,
			'message' => 'Cache tool executed.',
		);
	}

	/**
	 * Execute magic link support tool
	 */
	private static function execute_magic_link_support( $config, $context ) {
		$action = isset( $config['action'] ) ? $config['action'] : 'create';

		if ( 'create' === $action ) {
			// Generate secure token
			$token      = bin2hex( random_bytes( 16 ) );
			$expiry     = isset( $config['expiry_hours'] ) ? (int) $config['expiry_hours'] : 24;
			$expires_at = time() + ( $expiry * HOUR_IN_SECONDS );

			$links           = get_option( 'wpshadow_magic_links', array() );
			$links[ $token ] = array(
				'created_at'  => current_time( 'timestamp' ),
				'expires_at'  => $expires_at,
				'created_by'  => get_current_user_id(),
				'description' => isset( $config['description'] ) ? sanitize_text_field( $config['description'] ) : 'Workflow-generated link',
			);

			update_option( 'wpshadow_magic_links', $links );

			$magic_url = add_query_arg( 'wpshadow_magic_token', $token, home_url() );

			return array(
				'success'    => true,
				'message'    => 'Magic link created.',
				'token'      => $token,
				'url'        => $magic_url,
				'expires_at' => $expires_at,
			);
		}

		if ( 'revoke' === $action ) {
			$token = isset( $config['token'] ) ? sanitize_text_field( $config['token'] ) : '';
			if ( empty( $token ) ) {
				return array(
					'success' => false,
					'message' => 'No token provided.',
				);
			}

			$links = get_option( 'wpshadow_magic_links', array() );
			if ( isset( $links[ $token ] ) ) {
				unset( $links[ $token ] );
				update_option( 'wpshadow_magic_links', $links );
			}

			return array(
				'success' => true,
				'message' => 'Magic link revoked.',
			);
		}

		return array(
			'success' => true,
			'message' => 'Magic link tool executed.',
		);
	}

	/**
	 * Send in-app notification
	 */
	private static function execute_notification( $config, $context ) {
		$title   = isset( $config['notification_title'] ) ? $config['notification_title'] : 'WPShadow Notification';
		$message = isset( $config['notification_message'] ) ? $config['notification_message'] : '';
		$type    = isset( $config['notification_type'] ) ? $config['notification_type'] : 'info';

		$notifications   = get_option( 'wpshadow_notifications', array() );
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
	 * Execute Kanban note creation
	 */
	private static function execute_kanban_note( $config, $context ) {
		if ( ! class_exists( '\\WPShadow\\Workflow\\Kanban_Note_Action' ) ) {
			return array(
				'success' => false,
				'message' => 'Kanban Note Action class not found.',
			);
		}

		$note_config = array(
			'title'        => isset( $config['title'] ) ? self::replace_variables( $config['title'], $context ) : 'Workflow Alert',
			'description'  => isset( $config['description'] ) ? self::replace_variables( $config['description'], $context ) : '',
			'status'       => isset( $config['status'] ) ? $config['status'] : 'detected',
			'severity'     => isset( $config['severity'] ) ? $config['severity'] : 'medium',
			'category'     => isset( $config['category'] ) ? $config['category'] : 'settings',
			'auto_dismiss' => isset( $config['auto_dismiss'] ) ? (int) $config['auto_dismiss'] : 0,
			'workflow_id'  => isset( $context['workflow_id'] ) ? $context['workflow_id'] : '',
			'trigger_at'   => isset( $context['trigger_name'] ) ? $context['trigger_name'] : current_time( 'mysql' ),
		);

		$result = \WPShadow\Workflow\Kanban_Note_Action::create( $note_config );

		return $result;
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
		$log   = get_option( 'wpshadow_workflow_executions', array() );
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

	/**
	 * Handle plugin/theme update check
	 */
	public static function handle_update_check() {
		if ( ! is_admin() ) {
			return;
		}

		// Get available updates
		$updates = self::check_updates();

		if ( ! empty( $updates ) ) {
			$context = array(
				'trigger_type'      => 'plugin_update',
				'available_updates' => $updates,
				'count'             => count( $updates ),
			);

			self::execute_matching_workflows( 'plugin_update_trigger', $context );
		}
	}

	/**
	 * Check for available plugin/theme updates
	 */
	private static function check_updates() {
		$updates = array();

		// Check plugin updates
		$plugin_updates = get_site_transient( 'update_plugins' );
		if ( ! empty( $plugin_updates->response ) ) {
			foreach ( $plugin_updates->response as $plugin => $data ) {
				$updates[] = array(
					'type'    => 'plugin',
					'slug'    => $plugin,
					'version' => $data->new_version,
				);
			}
		}

		// Check theme updates
		$theme_updates = get_site_transient( 'update_themes' );
		if ( ! empty( $theme_updates->response ) ) {
			foreach ( $theme_updates->response as $theme => $data ) {
				$updates[] = array(
					'type'    => 'theme',
					'slug'    => $theme,
					'version' => $data['new_version'],
				);
			}
		}

		return $updates;
	}

	/**
	 * Handle backup completion event
	 */
	public static function handle_backup_completed( $status ) {
		$context = array(
			'trigger_type'  => 'backup_completion',
			'backup_status' => $status,
			'timestamp'     => current_time( 'timestamp' ),
		);

		self::execute_matching_workflows( 'backup_completion_trigger', $context );
	}

	/**
	 * Handle database issues
	 */
	public static function handle_database_check() {
		global $wpdb;

		$issues = array();

		// Check database size
		$db_size = self::get_database_size();
		if ( $db_size ) {
			$issues[] = array(
				'type' => 'size',
				'size' => $db_size,
			);
		}

		// Check for corruption
		$corruption_check = $wpdb->get_results( 'CHECK TABLE ' . $wpdb->prefix . 'posts' );
		if ( ! empty( $corruption_check ) ) {
			foreach ( $corruption_check as $check ) {
				if ( 'error' === $check->Msg_type || 'warning' === $check->Msg_type ) {
					$issues[] = array(
						'type'    => 'corruption',
						'message' => $check->Msg_text,
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$context = array(
				'trigger_type' => 'database_issue',
				'issues'       => $issues,
			);

			self::execute_matching_workflows( 'database_trigger', $context );
		}
	}

	/**
	 * Get database size in MB
	 */
	private static function get_database_size() {
		global $wpdb;

		$size_query = "SELECT ROUND( SUM( data_length + index_length ) / 1024 / 1024, 2 ) AS 'size'
						FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'";

		$result = $wpdb->get_var( $size_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $result ? (float) $result : 0;
	}

	/**
	 * Handle error log entry
	 */
	public static function handle_error_logged( $error_level, $message ) {
		$context = Context_Builder::build_error_logged( $error_level, $message );
		self::execute_matching_workflows( 'error_log_trigger', $context );
	}

	/**
	 * Handle diagnostic run activity (manual, Guardian, or scheduled scans)
	 */
	public static function handle_activity_logged( $activity ) {
		if ( empty( $activity['action'] ) || 'diagnostic_run' !== $activity['action'] ) {
			return;
		}

		$context = Context_Builder::build_diagnostic_run( $activity );
		self::execute_matching_workflows( 'diagnostic_run_trigger', $context );
	}

	/**
	 * Handle query string trigger (external CRON / manual trigger)
	 * Uses secure random token instead of predictable workflow ID
	 * URL format: ?wpshadow_trigger=random_hash_token
	 */
	public static function handle_query_string_trigger() {
		if ( is_admin() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['wpshadow_trigger'] ) ) {
			return;
		}

		$provided_token = sanitize_text_field( wp_unslash( $_GET['wpshadow_trigger'] ) );
		$triggered      = false;

		// Get all workflows with manual_cron_trigger
		$workflows = Workflow_Manager::get_workflows();

		foreach ( $workflows as $workflow ) {
			if ( ! $workflow['enabled'] ) {
				continue;
			}

			foreach ( $workflow['blocks'] as $block ) {
				if ( $block['type'] !== 'trigger' || $block['id'] !== 'manual_cron_trigger' ) {
					continue;
				}

				$config       = isset( $block['config'] ) ? $block['config'] : array();
				$stored_token = isset( $config['trigger_token'] ) ? $config['trigger_token'] : '';
				$require_auth = isset( $config['require_auth'] ) ? $config['require_auth'] : true;
				$allowed_ips  = isset( $config['allowed_ips'] ) ? $config['allowed_ips'] : '';

				// Compare tokens using hash_equals for timing attack resistance
				if ( empty( $stored_token ) || ! hash_equals( $stored_token, $provided_token ) ) {
					continue;
				}

				// Check authentication if required
				if ( $require_auth && ! is_user_logged_in() ) {
					continue;
				}

				// Check IP allowlist if configured
				if ( ! empty( $allowed_ips ) ) {
					$allowed_ips_arr = array_map( 'trim', explode( ',', $allowed_ips ) );
					$client_ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

					if ( ! in_array( $client_ip, $allowed_ips_arr, true ) ) {
						continue;
					}
				}

				// Execute the workflow
				$context = array(
					'trigger_type' => 'manual_cron',
					'triggered_at' => current_time( 'timestamp' ),
					'method'       => 'query_string',
				);

				self::execute_workflow( $workflow, $context );
				$triggered = true;
			}
		}

		// Optional: Prevent index from being cached/served
		if ( $triggered ) {
			nocache_headers();
		}
	}
}
