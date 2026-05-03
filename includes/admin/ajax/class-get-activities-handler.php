<?php
/**
 * AJAX Handler: Get Activities with Optional Context Filtering
 *
 * Provides real-time activity updates filtered by page context
 * (e.g., only Tools activities on Tools page, only Reports on Reports page)
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Admin/AJAX
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\AJAX;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Activity_Logger;

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
	 * @since 0.6095
	 * @return void Dies after sending JSON response
	 */
	public static function handle(): void {
		// Verify request security
		self::verify_request( 'thisismyurl_shadow_get_activities', 'manage_options' );

		// Get parameters
		$context   = self::get_post_param( 'context', 'text', '', false );
		$limit     = self::get_post_param( 'limit', 'int', 10 );
		$offset    = self::get_post_param( 'offset', 'int', 0 );
		$timestamp = self::get_post_param( 'since', 'int', 0 );
		$report    = self::get_post_param( 'report', 'text', '', false );

		// Limit bounds
		$limit = min( max( $limit, 1 ), 100 );

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
	 * @since 0.6095
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
				'actions' => array( 'tool_run', 'tool_executed', 'deep_scan' ),
			),
			'utilities' => array(
				'actions' => array( 'tool_run', 'tool_executed', 'deep_scan' ),
			),
			'reports'   => array(
				'actions' => array( 'report_generated', 'report_scheduled', 'report_sent', 'data_cleanup_completed' ),
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
				'categories' => array( 'achievements' ),
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
	 * @since 0.6095
	 * @param array $activity Activity entry
	 * @return array Formatted activity
	 */
	private static function format_activity( array $activity ): array {
		$action_labels = array(
			'diagnostic_run'            => __( 'Diagnostic Run', 'thisismyurl-shadow' ),
			'diagnostic_failed'         => __( 'Diagnostic Failed', 'thisismyurl-shadow' ),
			'treatment_applied'         => __( 'Auto-fix Applied', 'thisismyurl-shadow' ),
			'treatment_undone'          => __( 'Auto-fix Undone', 'thisismyurl-shadow' ),
			'finding_status_change'     => __( 'Status Changed', 'thisismyurl-shadow' ),
			'finding_dismissed'         => __( 'Finding Dismissed', 'thisismyurl-shadow' ),
			'finding_excluded'          => __( 'Finding Excluded', 'thisismyurl-shadow' ),
			'finding_resolved'          => __( 'Finding Fixed', 'thisismyurl-shadow' ),
			'workflow_created'          => __( 'Workflow Created', 'thisismyurl-shadow' ),
			'workflow_executed'         => __( 'Workflow Executed', 'thisismyurl-shadow' ),
			'workflow_enabled'          => __( 'Workflow Enabled', 'thisismyurl-shadow' ),
			'workflow_disabled'         => __( 'Workflow Disabled', 'thisismyurl-shadow' ),
			'workflow_saved'            => __( 'Workflow Saved', 'thisismyurl-shadow' ),
			'workflow_deleted'          => __( 'Workflow Deleted', 'thisismyurl-shadow' ),
			'cache_cleared'             => __( 'Cache Cleared', 'thisismyurl-shadow' ),
			'tool_run'                  => __( 'Tool Executed', 'thisismyurl-shadow' ),
			'deep_scan'                 => __( 'Deep Scan Run', 'thisismyurl-shadow' ),
			'report_generated'          => __( 'Report Generated', 'thisismyurl-shadow' ),
			'report_scheduled'          => __( 'Report Scheduled', 'thisismyurl-shadow' ),
			'report_sent'               => __( 'Report Sent', 'thisismyurl-shadow' ),
			'data_cleanup_completed'    => __( 'Data Cleanup Completed', 'thisismyurl-shadow' ),
			'settings_changed'          => __( 'Settings Updated', 'thisismyurl-shadow' ),
			'site_settings_changed'     => __( 'Settings Updated', 'thisismyurl-shadow' ),
			'cache_settings_changed'    => __( 'Settings Updated', 'thisismyurl-shadow' ),
			'retention_setting_updated' => __( 'Settings Updated', 'thisismyurl-shadow' ),
		);

		$action     = $activity['action'] ?? '';
		$label      = $action_labels[ $action ] ?? ucwords( str_replace( '_', ' ', $action ) );
		$details    = $activity['details'] ?? '';
		$timestamp  = $activity['timestamp'] ?? current_time( 'timestamp' );
		$user_name  = $activity['user_name'] ?? __( 'System', 'thisismyurl-shadow' );
		$category   = $activity['category'] ?? '';
		$metadata   = $activity['metadata'] ?? array();
		$report_url = '';
		$report_label = '';

		if ( 'report_generated' === $action && ! empty( $metadata['report'] ) ) {
			$report_slug  = (string) $metadata['report'];
			$report_label = ucwords( str_replace( '-', ' ', $report_slug ) );
			if ( function_exists( 'thisismyurl_shadow_get_reports_catalog' ) ) {
				foreach ( thisismyurl_shadow_get_reports_catalog() as $item ) {
					if ( isset( $item['report'], $item['title'] ) && $item['report'] === $report_slug ) {
						$report_label = $item['title'];
						break;
					}
				}
			}
			$report_url = admin_url( 'admin.php?page=thisismyurl-shadow' );
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
add_action( 'wp_ajax_thisismyurl_shadow_get_activities', array( 'ThisIsMyURL\Shadow\Admin\AJAX\Get_Activities_Handler', 'handle' ) );
