<?php
/**
 * AJAX Handler: Get Activities with Optional Context Filtering
 *
 * Provides real-time activity updates filtered by page context
 * (e.g., only Tools activities on Tools page, only Reports on Reports page)
 *
 * @package WPShadow
 * @subpackage Admin/AJAX
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Activities AJAX Handler
 *
 * Returns activities filtered by optional context (page) for real-time updates
 * across the platform.
 */
class Get_Activities_Handler extends AJAX_Handler_Base {

	/**
	 * Handle activity retrieval request
	 *
	 * @since 1.6030.2148
	 * @return void Dies after sending JSON response
	 */
	public static function handle(): void {
		// Verify request security
		self::verify_request( 'wpshadow_get_activities', 'manage_options' );

		// Get parameters
		$context   = self::get_post_param( 'context', 'text', '', false );
		$limit     = self::get_post_param( 'limit', 'int', 10 );
		$offset    = self::get_post_param( 'offset', 'int', 0 );
		$timestamp = self::get_post_param( 'since', 'int', 0 );
		$report    = self::get_post_param( 'report', 'text', '', false );

		// Limit bounds
		$limit = min( max( $limit, 5 ), 100 );

		// Build filters based on context
		$filters = self::get_filters_for_context( $context );

		// If timestamp provided, get newer activities
		if ( $timestamp > 0 ) {
			$filters['date_from'] = gmdate( 'Y-m-d H:i:s', $timestamp );
		}

		// Get activities
		$report = sanitize_key( $report );

		if ( ! empty( $report ) ) {
			$result     = Activity_Logger::get_activities( $filters, Activity_Logger::MAX_ACTIVITIES, 0 );
			$activities = $result['activities'] ?? array();
			$activities = array_values(
				array_filter(
					$activities,
					static function ( array $activity ) use ( $report ) {
						$metadata = $activity['metadata'] ?? array();
						$logged_report = isset( $metadata['report'] ) ? sanitize_key( (string) $metadata['report'] ) : '';
						return $logged_report === $report;
					}
				)
			);

			$total      = count( $activities );
			$activities = array_slice( $activities, $offset, $limit );
		} else {
			$result     = Activity_Logger::get_activities( $filters, $limit, $offset );
			$activities = $result['activities'] ?? array();
			$total      = $result['total'] ?? 0;
		}

		// Format activities for display
		$formatted = array_map( array( self::class, 'format_activity' ), $activities );

		self::send_success( array(
			'activities' => $formatted,
			'total'      => $total,
			'limit'      => $limit,
			'offset'     => $offset,
			'context'    => $context,
		) );
	}

	/**
	 * Get filter rules based on page context
	 *
	 * @since 1.6030.2148
	 * @param string $context Page context slug
	 * @return array Filter configuration
	 */
	private static function get_filters_for_context( string $context ): array {
		$filters = array();

		// Map contexts to activity categories/actions
		$context_filters = array(
			'diagnostics' => array(
				'actions' => array(
					'diagnostic_run',
					'diagnostic_failed',
					'treatment_applied',
					'treatment_undone',
					'finding_status_change',
					'finding_dismissed',
					'finding_excluded',
					'finding_resolved',
				),
			),
			'tools'     => array(
				'actions' => array( 'tool_run', 'tool_executed', 'deep_scan', 'quick_scan' ),
			),
			'utilities' => array(
				'actions' => array( 'tool_run', 'tool_executed', 'deep_scan', 'quick_scan' ),
			),
			'reports'   => array(
				'actions' => array( 'report_generated', 'report_scheduled', 'report_sent', 'data_cleanup_completed' ),
			),
			'guardian'  => array(
				'actions' => array( 'guardian_enabled', 'guardian_disabled', 'guardian_execution', 'guardian_deep_scan', 'diagnostic_finding' ),
			),
			'workflows' => array(
				'actions' => array( 'workflow_created', 'workflow_executed', 'workflow_enabled', 'workflow_disabled', 'workflow_saved', 'workflow_deleted' ),
			),
			'settings'  => array(
				'actions' => array( 'settings_changed', 'site_settings_changed', 'cache_settings_changed', 'retention_setting_updated' ),
			),
			'training'  => array(
				'categories' => array( 'academy' ),
			),
			'achievements' => array(
				'categories' => array( 'gamification' ),
			),
			'security'  => array(
				'categories' => array( 'security' ),
			),
			'performance' => array(
				'categories' => array( 'performance' ),
			),
		);

		// Apply context-specific filters
		if ( ! empty( $context ) && isset( $context_filters[ $context ] ) ) {
			$context_config = $context_filters[ $context ];

			// Filter by actions if specified
			if ( ! empty( $context_config['actions'] ) ) {
				$filters['actions'] = $context_config['actions'];
			}

			// Filter by category if specified
			if ( ! empty( $context_config['categories'] ) ) {
				$filters['categories'] = $context_config['categories'];
			}
		}

		return $filters;
	}

	/**
	 * Format activity for frontend display
	 *
	 * @since 1.6030.2148
	 * @param array $activity Activity entry
	 * @return array Formatted activity
	 */
	private static function format_activity( array $activity ): array {
		$action_labels = array(
			'diagnostic_run'            => __( 'Diagnostic Run', 'wpshadow' ),
			'diagnostic_failed'         => __( 'Diagnostic Failed', 'wpshadow' ),
			'treatment_applied'         => __( 'Auto-fix Applied', 'wpshadow' ),
			'treatment_undone'          => __( 'Auto-fix Undone', 'wpshadow' ),
			'finding_status_change'     => __( 'Status Changed', 'wpshadow' ),
			'finding_dismissed'         => __( 'Finding Dismissed', 'wpshadow' ),
			'finding_excluded'          => __( 'Finding Excluded', 'wpshadow' ),
			'finding_resolved'          => __( 'Finding Fixed', 'wpshadow' ),
			'workflow_created'          => __( 'Workflow Created', 'wpshadow' ),
			'workflow_executed'         => __( 'Workflow Executed', 'wpshadow' ),
			'workflow_enabled'          => __( 'Workflow Enabled', 'wpshadow' ),
			'workflow_disabled'         => __( 'Workflow Disabled', 'wpshadow' ),
			'workflow_saved'            => __( 'Workflow Saved', 'wpshadow' ),
			'workflow_deleted'          => __( 'Workflow Deleted', 'wpshadow' ),
			'guardian_enabled'          => __( 'Guardian Enabled', 'wpshadow' ),
			'guardian_disabled'         => __( 'Guardian Disabled', 'wpshadow' ),
			'guardian_execution'        => __( 'Guardian Executed', 'wpshadow' ),
			'guardian_deep_scan'        => __( 'Deep Scan Executed', 'wpshadow' ),
			'cache_cleared'             => __( 'Cache Cleared', 'wpshadow' ),
			'tool_run'                  => __( 'Tool Executed', 'wpshadow' ),
			'deep_scan'                 => __( 'Deep Scan Run', 'wpshadow' ),
			'quick_scan'                => __( 'Quick Scan Run', 'wpshadow' ),
			'report_generated'          => __( 'Report Generated', 'wpshadow' ),
			'report_scheduled'          => __( 'Report Scheduled', 'wpshadow' ),
			'report_sent'               => __( 'Report Sent', 'wpshadow' ),
			'data_cleanup_completed'    => __( 'Data Cleanup Completed', 'wpshadow' ),
			'settings_changed'          => __( 'Settings Updated', 'wpshadow' ),
			'site_settings_changed'     => __( 'Settings Updated', 'wpshadow' ),
			'cache_settings_changed'    => __( 'Settings Updated', 'wpshadow' ),
			'retention_setting_updated' => __( 'Settings Updated', 'wpshadow' ),
		);

		$action     = $activity['action'] ?? '';
		$label      = $action_labels[ $action ] ?? ucwords( str_replace( '_', ' ', $action ) );
		$details    = $activity['details'] ?? '';
		$timestamp  = $activity['timestamp'] ?? current_time( 'timestamp' );
		$user_name  = $activity['user_name'] ?? __( 'System', 'wpshadow' );
		$category   = $activity['category'] ?? '';
		$metadata   = $activity['metadata'] ?? array();
		$report_url = '';
		$report_label = '';

		if ( 'report_generated' === $action && ! empty( $metadata['report'] ) ) {
			$report_slug  = (string) $metadata['report'];
			$report_label = ucwords( str_replace( '-', ' ', $report_slug ) );
			if ( function_exists( 'wpshadow_get_reports_catalog' ) ) {
				foreach ( wpshadow_get_reports_catalog() as $item ) {
					if ( isset( $item['report'], $item['title'] ) && $item['report'] === $report_slug ) {
						$report_label = $item['title'];
						break;
					}
				}
			}
			$report_url = admin_url( add_query_arg( array( 'page' => 'wpshadow-reports', 'report' => $report_slug ), 'admin.php' ) );
			$details    = '';
		}

		return array(
			'id'        => $activity['id'] ?? '',
			'action'    => $label,
			'details'   => $details,
			'timestamp' => $timestamp,
			'time_ago'  => human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ago',
			'user_name' => $user_name,
			'category'  => $category,
			'metadata'  => $metadata,
			'report_url' => $report_url,
			'report_label' => $report_label,
		);
	}
}

// Register AJAX handler
add_action( 'wp_ajax_wpshadow_get_activities', array( 'WPShadow\Admin\AJAX\Get_Activities_Handler', 'handle' ) );
